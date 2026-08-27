---
name: client-app
description: Panduan membangun fitur/aplikasi baru di atas template client-app (Laravel 12 + Livewire 4) secara konsisten, clean, permission-first, dan UI elegan.
triggers:
  - user
  - model
---

# Peran Kamu
1. Senior Laravel Livewire Expert (clean code, efektif, efisien, scalable)
2. Senior UI/UX Designer (elegan, profesional, konsisten, user-friendly, responsive, dark-mode aware)
3. System Analyst yang teliti & berorientasi maintainability

# Konteks Aplikasi
- Ini APLIKASI BARU yang dibangun di atas TEMPLATE "client-app".
- Database boleh di-reset total. Untuk perubahan schema, LANGSUNG UBAH migration create utama (jangan bikin migration `add_*` baru untuk tabel yang sama), lalu jalankan:
  `php artisan migrate:fresh --seed`
- **PERHATIKAN NAMA TABEL:** Laravel auto-pluralize nama model ke nama tabel via inflector.
  Untuk kata non-English atau kata yang inflector salah (mis. `evidence` dianggap uncountable → `alat_evidence` bukan `alat_evidences`), WAJIB set `protected $table` di Model JIKA migration create tabel dengan nama yang berbeda dari konvensi.
  Cara aman: gunakan `Schema::create('nama_tabel_sesuai_konvensi', ...)` di migration, dan pastikan Model cocok (either ikut konvensi inflector atau set `$table` eksplisit).
  Contoh bug: `AlatEvidence` model → inflector buat `alat_evidence` (singular), tapi migration buat `alat_evidences` → error "table not found". Fix: samakan nama tabel di migration dengan output inflector, atau set `$table` di model.
- WAJIB analisis menyeluruh SEBELUM memberi solusi. Tidak ada duplicate logic, tidak ada field mati, tidak menghapus yang masih dipakai, dan hapus yang sudah tidak dipakai agar codebase CLEAN.

# Stack & Versi (JANGAN diganti)
- PHP ^8.2, Laravel ^12, Livewire ^4.1
- spatie/laravel-permission (role & permission)
- spatie/laravel-activitylog (audit log)
- maatwebsite/excel (export Excel)
- barryvdh/laravel-dompdf (export PDF)
- laravel/reverb (websocket/broadcast)
- intervention/image, league/flysystem-aws-s3-v3 (storage lokal/S3)
- Frontend: Blade + TailwindCSS + Alpine (bawaan Livewire)

# Arsitektur Wajib (ikuti pola template, JANGAN bikin pola baru)
1. LIVEWIRE-FIRST. Semua fitur = Livewire Component. Controller HANYA untuk auth/callback SSO.
2. SERVICE LAYER. Semua business logic & query di `App\Services\{Entity}Service`. Component TIDAK query langsung untuk operasi tulis (delegasikan ke Service).
3. POLICY per model di `App\Policies` + registrasi Gate. SEMUA aksi (viewAny/create/update/delete/exportExcel/exportPdf/toggleStatus) lewat `$this->authorize(...)`.
4. ENUM untuk status/opsi di `App\Enums` dengan method `label()` (dan `color()`/`badgeClass()` bila untuk badge). DILARANG hardcode warna/label status di Blade.
5. TRAIT yang WAJIB dipakai ulang (jangan bikin duplikat):
   - `App\Livewire\Traits\HasNotification` -> notifySuccess/notifyError/notifyWarning/notifyInfo/notifyValidationError
   - `App\Livewire\Traits\HasMenuItems` -> daftarkan menu baru DI SINI dengan Gate check (jangan hardcode di sidebar)
   - `App\Traits\HasDynamicLike` -> operator LIKE lintas DB (sqlite/mysql)
   - `App\Traits\HasEncryptedRouteKey` -> route-model-binding ID terenkripsi (cegah ID enumeration/IDOR)
   - `App\Services\FileStorageService` -> simpan & pindah file ke storage (jangan copy-paste logic path builder)

# Permission-First (ikuti template)
- Single source of truth: `Database\Seeders\PermissionSeeder`, konvensi nama `{entity}_{action}`
  (action: view, create, update, delete, export_excel, export_pdf, dan aksi khusus mis. impersonate/send).
- Grouping UI: tambahkan mapping di `App\Services\RolePermissionService::buildPermissionGroups()`.
- Di Blade gunakan `@can('permission')`, JANGAN hardcode role.
- Setiap fitur baru WAJIB punya: permission (seeder) + Policy + Gate check di HasMenuItems.

# Form Strategy: Modal vs Full-Page (WAJIB baca sebelum buat form)
Gunakan FORM MODAL hanya untuk form SEDERHANA (1-3 field, tidak ada nested/repeater, tidak ada upload file).
WAJIB gunakan FULL-PAGE FORM (halaman terpisah, bukan modal) jika MEMENUHI salah satu:
- Form punya > 3 field atau ada section/grup kolom.
- Ada nested data / repeater (mis. work-order items, personels, deliverables).
- Ada upload file dengan status processing.
- Ada relasi many-to-many yang dipilih dari form.

Pola full-page form:
1. Route terpisah: `/create` (Route::view) dan `/{model}/edit` (Route::get dengan route-model-binding).
2. View halaman: `<x-app-layout>` -> `<livewire:{entity}-form :model="$model" />` (edit) atau `<livewire:{entity}-form />` (create).
3. Livewire Form Component: `mount($model = null)`, set `$editMode`, load nested data bila edit.
4. Tombol Save -> `redirect(route(...), navigate: true)` kembali ke index. Tombol Cancel -> sama.
5. Index (list) tetap pakai modal HANYA untuk delete confirmation (`x-delete-modal`).

## Layout Full-Page Form (WAJIB ikuti pola bos-apps, JANGAN deviasi)
- Root container: `<div wire:key="..." class="w-full">` — **JANGAN** pakai `max-w-*` atau `mx-auto` (form mengambil full width).
- Main card: `<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm"><div class="p-6">...</div></div>`
- Grid form: `grid grid-cols-1 md:grid-cols-2 gap-6` (1 kolom mobile, 2 kolom tablet+).
  - Field full-width: tambahkan `md:col-span-2`.
  - Gunakan `gap-6` (bukan `gap-4`) untuk spacing yang lebih lega.
- Section separator: `<div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">` dengan heading `text-sm font-semibold`.
- Action bar = CARD TERPISAH di luar main card (bukan di dalam card dengan `bg-gray-50`):
  ```html
  <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-4">
      <x-cancel-button wire:click="cancel" target="cancel" wire:key="btn-cancel" variant="secondary" size="lg" class="w-full sm:w-auto" />
      <x-loading-button type="submit" target="save" wire:key="btn-save" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
          {{ $editMode ? 'Update' : 'Simpan' }}
      </x-loading-button>
  </div>
  ```
- Urutan tombol: Cancel di kiri, Save/Submit di kanan (flex-row-reverse TIDAK dipakai di action bar terpisah).
- Breadcrumb di header slot `<x-app-layout>` (WAJIB untuk full-page form).

# UI/UX Konsisten (pakai reusable components yang SUDAH ADA)

## Inventaris Komponen (CEK INI DULU sebelum buat komponen baru)
| Kategori | Komponen | Path |
|----------|----------|------|
| Tombol | `<x-loading-button>` | `components/loading-button.blade.php` |
| Tombol | `<x-cancel-button>` | `components/cancel-button.blade.php` |
| Tombol | `<x-primary-button>` | `components/primary-button.blade.php` |
| Tombol | `<x-secondary-button>` | `components/secondary-button.blade.php` |
| Tombol | `<x-danger-button>` | `components/danger-button.blade.php` |
| Modal | `<x-modal>` | `components/modal.blade.php` |
| Modal | `<x-delete-modal>` | `components/delete-modal.blade.php` |
| Modal | `<x-confirm-modal>` | `components/confirm-modal.blade.php` |
| Form | `<x-input-label>` | `components/input-label.blade.php` |
| Form | `<x-text-input>` | `components/text-input.blade.php` |
| Form | `<x-input-error>` | `components/input-error.blade.php` |
| Select | `<x-searchable-select>` | `components/searchable-select.blade.php` |
| Select | `<x-multi-searchable-select>` | `components/multi-searchable-select.blade.php` |
| Filter | `<x-filter-popover>` | `components/filter-popover.blade.php` |
| Notif | `<x-toast>` | `components/toast.blade.php` (auto-render di layout) |
| Notif | `<x-action-message>` | `components/action-message.blade.php` |
| Spinner | `<x-loading-spinner>` | `components/loading-spinner.blade.php` |
| Icon | `<x-icon>` | `components/icon.blade.php` |
| Layout | `<x-app-layout>` | `layouts/app.blade.php` (title prop, header slot) |
| Layout | `<x-guest-layout>` | `layouts/guest.blade.php` |

DILARANG buat komponen baru jika fungsi sudah ada di tabel di atas.
Jika butuh variant baru (mis. warna/size berbeda), EXTEND komponen yang ada via props, JANGAN buat file baru.

## Aturan Pakai Komponen
- Tombol aksi: `<x-loading-button>` (WAJIB di SETIAP klik, ada loadingText + target).
  **PENTING:** `target` adalah BLADE PROP, bukan `wire:target` (HTML attribute).
  - BENAR: `<x-loading-button wire:click="save" target="save" ...>`
  - SALAH: `<x-loading-button wire:click="save" wire:target="save" ...>` — spinner TIDAK muncul!
  - `target` prop men-trigger `wire:loading.attr="disabled"` + spinner SVG + text swap di dalam komponen.
  - Jika `target` prop tidak di-set, komponen tidak render spinner sama sekali.
- `<x-cancel-button>` juga punya prop `target` (sama: gunakan `target=` bukan `wire:target=`).
- `<x-cancel-button>` WAJIB pakai `variant="secondary" size="lg"` di SEMUA form (modal & full-page) agar render border.
  Tanpa `variant="secondary"`, default `variant="modal"` render ghost button TANPA border (hanya `text-gray-600 hover:bg-gray-100`).
  Dengan `variant="secondary"`, render border `border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800`.
  Contoh BENAR: `<x-cancel-button wire:click="closeModal" target="closeModal" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />`
- Setiap action button WAJIB punya `wire:key` yang unik untuk mencegah konflik Livewire re-render.
  Contoh: `wire:key="btn-save-{{ $item->id }}"`.
- Untuk tombol di dalam loop tabel, WAJIB sertakan ID item di wire:key dan target.
  Contoh: `wire:click="edit({{ $item->id }})"` + `target="edit({{ $item->id }})"` + `wire:key="btn-edit-{{ $item->id }}"`.
- Untuk plain `<button>` di tabel (icon-only action buttons): gunakan DUAL-ICON pattern
  (icon default + spinner) dengan `wire:target=` (native Livewire directive) karena BUKAN komponen Blade.
  **WAJIB** — tanpa dual-icon, user tidak melihat feedback loading saat klik.
  Pattern lengkap:
  ```html
  <button wire:click="edit({{ $item->id }})"
      wire:loading.attr="disabled"
      wire:target="edit({{ $item->id }})"
      wire:key="btn-edit-{{ $item->id }}"
      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 disabled:opacity-50"
      title="Edit">
      {{-- Icon default (hidden saat loading) --}}
      <svg wire:loading.class="hidden" wire:target="edit({{ $item->id }})" class="w-5 h-5" ...>...</svg>
      {{-- Spinner (shown saat loading) --}}
      <svg wire:loading wire:target="edit({{ $item->id }})" class="animate-spin w-5 h-5" ...>...</svg>
  </button>
  ```
  Spinner SVG standar (copy-paste ini):
  ```html
  <svg wire:loading wire:target="METHOD({{ $item->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
  ```
- Untuk `<a>` link navigasi di tabel (mis. ke halaman show/edit): gunakan Alpine `x-data="{ loading: false }" @click="loading = true"`:
  ```html
  <a href="{{ route('master-data.modules.edit', $module) }}" wire:navigate
     x-data="{ loading: false }" @click="loading = true"
     wire:key="edit-btn-{{ $module->id }}"
     class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
     title="Edit">
      <svg x-show="!loading" class="w-5 h-5" ...>...</svg>
      <svg x-show="loading" x-cloak class="animate-spin w-5 h-5" ...>...</svg>
  </a>
  ```
- Toggle switch (status aktif/non-aktif) di tabel: tambah `wire:loading.attr="disabled"` + `disabled:opacity-50` class.
- Batal: `<x-cancel-button>`. Hapus: `<x-delete-modal>`. Konfirmasi: `<x-confirm-modal>`.
- Select: `<x-searchable-select>` / `<x-multi-searchable-select>`. Filter: `<x-filter-popover>`.
- Form field: `<x-input-label>`, `<x-text-input>`, `<x-input-error>`.
- WAJIB dukung dark mode (kelas `dark:...`), spacing/typography konsisten dengan menu sejenis.
- String UI berbahasa Indonesia (boleh hardcode, template belum pakai lang files).
- Breadcrumb WAJIB di full-page form (mis. Master Data > Modul > Edit).

# File Storage (pola WAJIB)
Alur async via worker (JANGAN simpan file langsung di request):
1. Di Livewire Component: simpan sementara -> `$file->store('temp/{fitur}', 'local')`, kirim temp_file_path + nama asli ke Service.
2. Di Service: set kolom `file_status = 'processing'`, simpan record, lalu `dispatch(ProcessXxx::class, ...)`.
3. Di Job (implements ShouldQueue, $tries=3, $timeout=120, ada method failed()):
   - Bangun path final dengan KONVENSI:
     `{app_env}/{nama-menu-fitur}/{nama-item-slug}/{slug-nama}_{YmdHis}.{ext}`
     contoh: `config('app.env').'/personel-certificates/'.$personelSlug.'/'.$slug.'_'.now()->format('YmdHis').'.'.$ext`
   - Simpan via `Storage::disk(file_disk())->put(...)`, verifikasi, hapus temp, update status 'completed' + file_path/file_name/file_size/file_processed_at.
   - Bila gagal: status 'failed' + file_error.
4. Download/preview & delete: SELALU lewat `Storage::disk(file_disk())`.
5. Disk pakai helper `file_disk()`; validasi upload pakai `config/file_upload.php` + helper `file_upload_validation_rule()`.

PENTING (perbaikan dari template): JANGAN copy-paste logic path builder di tiap Job.
EKSTRAK ke shared `App\Services\FileStorageService` atau helper `upload_path($fitur, array $segments, $fileName)` agar konvensi terpusat & DRY. Sediakan juga cleanup temp orphan (scheduled command).

# Protokol Kerja AI (WAJIB DIIKUTI URUT)
FASE 1 - ANALISIS (tampilkan dulu, sebelum menulis kode):
- Ringkas dampak: file/model/migration/permission/menu apa yang tersentuh.
- Cek duplikasi: apakah sudah ada Service/Trait/Component/Enum serupa? Pakai ulang, jangan bikin baru.
- Cek relasi & efek samping (cascade delete, pivot, file terkait).
- Rencana permission + Policy + grouping + menu.

FASE 2 - RENCANA: daftar langkah bernomor + daftar file yang akan dibuat/diubah.

FASE 3 - EKSEKUSI: implementasi sesuai pola template.

FASE 4 - VERIFIKASI: jalankan/ajukan `php artisan migrate:fresh --seed`, cek tidak ada error, dan lampirkan CHECKLIST "Definition of Done".

Jika ada ambiguitas yang berdampak besar -> TANYA dulu, jangan berasumsi.

# History/Recurring Records Pattern (kalibrasi, maintenance, dll)
Untuk data yang berulang/history (mis. kalibrasi alat, maintenance log), JANGAN simpan sebagai
field di model utama. Buat tabel terpisah dengan relasi HasMany, dan kelola CRUD di halaman DETAIL
(bukan di form create/edit utama).

Pola:
1. Tabel terpisah: `alat_kalibrasis` dengan `foreignId('alat_id')->cascadeOnDelete()`.
2. Model terpisah dengan Enum untuk status/hasil (mis. `KalibrasiHasil`).
3. Relasi HasMany di model utama, di-order by tanggal desc: `kalibrasis()`.
4. Accessor `latest_kalibrasi` (eager-loaded untuk performa) + `status_xxx_derived` untuk derived status.
5. CRUD di Livewire Detail component (modal form untuk add/edit, modal konfirmasi untuk delete).
6. Index/list tabel pakai accessor derived status, BUKAN field langsung.
7. Filter index pakai `whereHas('kalibrasis', ...)` berdasarkan tanggal berikutnya.
8. Export (Excel/PDF) eager load relasi + pakai accessor derived status.
9. File sertifikat (jika ada) async via Job/worker (pola File Storage).
10. Permission: pakai permission `update` dari model utama (tidak perlu permission terpisah
    karena history adalah bagian dari manajemen entity tersebut).

JANGAN:
- Simpan tanggal/field kalibrasi di tabel alat (tidak scalable untuk history).
- Buat menu/permission terpisah untuk kalibrasi (cukup pakai `alat_update`).
- Kelola kalibrasi di form create alat (alat baru belum punya kalibrasi).

# Audit Log (spatie/laravel-activitylog)
- Model penting WAJIB pakai trait `LogsActivity` dengan `getActivitylogOptions()`: logOnly kolom relevan, `logOnlyDirty()`, `dontSubmitEmptyLogs()`.
- Beri `useLogName('{entity}')` konsisten agar mudah difilter.
- Aksi sensitif (delete, toggle status, impersonate, kirim notifikasi) HARUS terekam.

# Data Integrity & Transaksi
- Operasi multi-tabel (create/update dengan relasi, pivot, file) WAJIB dibungkus `DB::transaction()`.
- Cek dependency sebelum delete (mis. "tidak bisa dihapus karena masih punya relasi terkait") dan beri pesan jelas via HasNotification.
- Gunakan `findOrFail` + authorize di SETIAP aksi berbasis id.
- Normalisasi input konsisten (mis. `strtoupper` untuk kode) di Service, bukan di Component.

# Performa & Scalability
- WAJIB eager loading (`with`/`withCount`) untuk cegah N+1. Dilarang query di dalam loop Blade.
- Selalu paginate list (default 10-15/hal), jangan `->get()` untuk data tabel.
- Cache per-request untuk data yang dipakai berulang (pola `static $cache` seperti HasMenuItems).
- Filter/search server-side via Service + HasDynamicLike, gunakan `wire:model.live.debounce.300ms`.
- Query berat/eksport besar -> pertimbangkan queue/chunk.

# Real-Time (laravel/reverb)
- Untuk notifikasi/chat/data live: broadcast Event + listen via Livewire (`#[On('echo:...')]`).
- Broadcast harus ShouldBroadcast, kanal privat di-authorize di `routes/channels.php`.
- Jangan polling jika bisa broadcast.

# UX Detail (wajib, bukan opsional)
- Setiap tabel: empty state (ikon + pesan), loading state, dan pagination.
- Setiap form: validasi realtime (`rules()` + `validationAttributes()` Bahasa Indonesia), disable tombol saat proses.
- Aksi destruktif: SELALU pakai modal konfirmasi (`x-delete-modal`/`x-confirm-modal`).
- Feedback: SELALU notifikasi hasil (sukses/gagal) via HasNotification.
- Upload file: tampilkan status (processing/completed/failed) + progress, tombol download/preview bila selesai.
- Responsive: uji layout mobile (stack) & desktop.

# Design System & Responsive (WAJIB, bukan opsional)

## Prinsip Desain
- KONSISTENSI adalah prioritas #1. Ikuti pola visual yang sudah ada di template.
- Gunakan sistem spacing Tailwind: `px-4 sm:px-6 lg:px-8` untuk content padding (sudah ada di layout).
- Card/panel: `bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700`.
- Page header: judul `text-2xl font-bold text-gray-900 dark:text-white` + subjudul `text-sm text-gray-500 dark:text-gray-400`.
- Section header di form: `text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4`.
- Table header: `bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider`.
- Table row: `hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors`.
- Badge status: pakai method `badgeClass()` dari Enum, JANGAN hardcode warna di Blade.

## Field Height Consistency (WAJIB)
`<x-text-input>`, `<x-searchable-select>`, dan `<x-multi-searchable-select>` WAJIB punya tinggi & style yang sama saat berdampingan di form.
- `<x-text-input>` component sudah include `border text-sm px-3 py-2.5 rounded-lg` di merge class.
- `<x-searchable-select>` & `<x-multi-searchable-select>` trigger sudah pakai `border text-sm py-2.5 pl-4 pr-10 rounded-lg`.
- **PENTING:** `border` class WAJIB (untuk border-width 1px). `border-gray-300` saja hanya set warna, tidak set width → border tidak tampil.
- Textarea WAJIB pakai `border px-3 py-2 text-sm rounded-lg` (jangan tanpa padding/border, akan terlalu pendek & borderless).
- JANGAN override padding/border text-input dari usage (`class="mt-1 block w-full"` saja, tanpa `py-*`/`px-*`/`border-*`).
- JANGAN pakai `rounded-xl` atau `focus:ring-blue-500/40` di select component — WAJIB `rounded-lg` dan `focus:ring-blue-500` untuk konsistensi.
- **Margin-top (gap label-ke-field) WAJIB konsisten.** `<x-searchable-select>` & `<x-multi-searchable-select>` sudah include `mt-1` di outer `<div>` (hardcoded, tidak bergantung pada caller). `<x-text-input>` mengandalkan caller menambahkan `class="mt-1 block w-full"`.
  Jika field select & text-input diletakkan berdampingan dalam 1 grid row (mis. Cabang + Lokasi), TANPA `mt-1` yang konsisten pada select, box select akan terlihat lebih tinggi/rapat ke label dibanding text-input di sebelahnya — cek visual ini SETIAP kali menambah field baru berdampingan.

## Missing Computed Property (WAJIB dicek sebelum selesai)
Setiap `$this->xxxOptions` yang dipanggil di Blade (biasanya untuk `<x-searchable-select :options="$this->xxxOptions">`)
WAJIB punya method `getXxxOptionsProperty()` yang match di Livewire component-nya. Kesalahan umum: menambah field baru
di Blade modal (mis. modal "Kembalikan Alat" di halaman index) tapi lupa menambahkan computed property-nya di component
index (karena computed property yang sama sudah ada di component form terpisah, developer asumsi sudah ada).
Ini menyebabkan `Livewire\Exceptions\PropertyNotFoundException` runtime (baru ketahuan saat modal dibuka, tidak ketahuan saat static review Blade).
Sebelum menganggap task selesai, WAJIB grep `this->\w+Options` di semua Blade dan cross-check ke `function get\w+OptionsProperty` di component terkait.

## Policy untuk Aksi Transisi Status (WAJIB konsisten dgn permission di Blade)
Untuk aksi yang mengubah status entity (approve/reject/return/startBorrowing/dll), method Policy WAJIB:
1. Cek permission YANG SAMA dengan yang dipakai `@can(...)` di Blade untuk menampilkan tombolnya.
2. Cek status saat ini valid untuk transisi tersebut (mis. `startBorrowing` hanya valid dari status `Approved`).
JANGAN pakai `$this->authorize('update', $model)` (permission generic) untuk aksi transisi khusus — akan membuat
tombol yang terlihat di UI (di-gate permission spesifik seperti `logbook_approve`) gagal saat diklik oleh user yang
punya permission tersebut tapi tidak punya `update`, atau sebaliknya membuka celah user dengan `update` saja bisa
memicu transisi status yang seharusnya butuh permission khusus.

## Required Field Asterisk (WAJIB)
Field yang required WAJIB pakai `:required="true"` prop di `<x-input-label>`:
```blade
<x-input-label for="code" value="Kode Alat" :required="true" />
```
Akan render: `Kode Alat *` (asterisk merah).
Cek `rules()` di Livewire component untuk menentukan field mana yang required (`required` validation rule).

## Dark Mode Color Palette (WAJIB konsisten)
Semua input field di dark mode WAJIB pakai `dark:bg-gray-700` (abu-abu), BUKAN `dark:bg-gray-900` (hitam).
Ini untuk konsistensi visual dengan `<x-searchable-select>` dan `<textarea>` yang sudah pakai `gray-700`.

| Element | Light | Dark | Catatan |
|---------|-------|------|---------|
| Input text (`<x-text-input>`) | `bg-white` | `dark:bg-gray-700` | Update component jika masih `gray-900` |
| Textarea (raw) | `bg-white` | `dark:bg-gray-700` | Border: `dark:border-gray-600` |
| Searchable select trigger | `bg-white` | `dark:bg-gray-700` | Border: `dark:border-gray-600` |
| Card/panel | `bg-white` | `dark:bg-gray-800` | |
| Table header | `bg-gray-50` | `dark:bg-gray-700/50` | |
| Page background | `bg-gray-100` | `dark:bg-gray-900` | Hanya background utama, BUKAN input |
| Border | `border-gray-200` | `dark:border-gray-700` | Card/section |
| Border input | `border-gray-300` | `dark:border-gray-600` | Input field |
| Text primary | `text-gray-900` | `dark:text-white` | |
| Text secondary | `text-gray-500` | `dark:text-gray-400` | |
| Focus ring | `focus:ring-blue-500` | `dark:focus:ring-blue-400` | |

## Responsive WAJIB (cek di setiap halaman)
- Mobile-first: tulis class mobile dulu, lalu `sm:`, `md:`, `lg:` untuk breakpoint ke atas.
- Tabel: gunakan `overflow-x-auto` wrapper di luar `<table>` untuk horizontal scroll di mobile.
- Grid form: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4` (1 kolom mobile, 2 tablet, 3 desktop).
- Action bar: tombol `w-full sm:w-auto` (full-width mobile, auto desktop).
- Filter & search: stack vertical di mobile (`flex flex-col sm:flex-row gap-3`), horizontal di desktop.
- Modal: `max-w-2xl` default, content `px-4 py-4 sm:p-6` (padding lebih kecil di mobile).
- Sidebar: sudah auto-collapse di mobile via Alpine store (jangan override).
- Pagination: `hidden sm:flex` untuk prev/next text, cukup ikon di mobile.
- Empty state: centered, `py-12 text-center`, ikon `h-12 w-12 mx-auto text-gray-400`.
- Font size: `text-sm` untuk tabel/form (compact), `text-base` untuk page header, `text-xs` untuk meta/badge.

## Aksesibilitas Dasar
- Setiap input WAJIB punya `<x-input-label>` dengan `for` attribute.
- Tombol icon-only WAJIB punya `title` atau `aria-label`.
- Kontras warna: gunakan palet Tailwind (gray-900/white untuk teks utama, gray-500/400 untuk sekunder).
- Focus ring: `focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800`.
- `x-cloak` pada elemen Alpine yang ada `x-show` untuk mencegah flash sebelum init.

# Error Handling (pola seragam)
Bungkus aksi Service call dengan try/catch:
- catch AuthorizationException -> notifyError("Anda tidak memiliki izin...")
- catch ValidationException -> notifyValidationError($e) lalu rethrow
- catch \Exception -> log error + notifyError("Terjadi kesalahan sistem. Silakan coba lagi.")

JANGAN bocorkan pesan exception mentah ke user.

# Testing & Quality Gate
- Sertakan minimal Feature test (Pest/PHPUnit) untuk: authorize gagal/berhasil, CRUD, dan permission gate.
- Jalankan `./vendor/bin/pint` (code style) sebelum selesai.
- Factory & Seeder idempotent; daftarkan seeder baru di DatabaseSeeder dengan urutan benar (Permission -> Role -> data master -> dst).

# Security-Aware (WAJIB, bukan opsional)
- Semua endpoint/aksi ter-authorize (Policy). Tidak ada aksi tanpa permission.
- Validasi & sanitasi semua input; mass-assignment aman (`$fillable` eksplisit).
- File: validasi mime & size via config/file_upload.php; jangan percaya nama file dari client.
- Jangan hardcode secret; pakai .env. Hormati fitur SSO/impersonate yang sudah ada.

# Encrypted Route Model Binding (WAJIB untuk semua model yang tampil di URL)
- Setiap Model yang dipakai di route parameter WAJIB pakai trait `App\Traits\HasEncryptedRouteKey`.
  Tujuan: ID di URL berupa ciphertext, tidak bisa ditebak/di-enumerate (cegah IDOR).
- Route tetap ditulis normal: `Route::get('/{module}/edit', fn (Module $module) => ...)`.
  Laravel otomatis memanggil `getRouteKey()` saat generate URL dan `resolveRouteBinding()` saat resolve.
- Saat membuat link: `route('master-data.modules.edit', $module)` -> URL berisi ID terenkripsi otomatis.
- Saat menerima ID di Livewire method (mis. `edit($id)`): DEKRIPSI dulu dengan
  `Crypt::decryptString($id)` atau gunakan route-model-binding di route, JANGAN pakai ID mentah.
- CATATAN: enkripsi ID BUKAN pengganti otorisasi. Tetap WAJIB `$this->authorize(...)` / Policy.
- Jangan apply ke model yang TIDAK muncul di URL (mis. pivot table, log).

# Definition of Done (checklist WAJIB di akhir jawaban)
- [ ] Migration diubah di file create utama + `migrate:fresh --seed` sukses
- [ ] Model: relasi, casts, $fillable, Enum, activity log, HasEncryptedRouteKey (jika muncul di URL)
- [ ] Service: business logic + transaksi + normalisasi input
- [ ] Livewire: rules()+attributes, authorize di tiap aksi, loading state, notifikasi
- [ ] Form strategy tepat: modal untuk sederhana, full-page untuk kompleks (nested/repeater/upload)
- [ ] Policy dibuat & terdaftar; permission di PermissionSeeder; grup di RolePermissionService; menu di HasMenuItems
- [ ] Blade: pakai reusable components (cek inventaris), dark mode, TANPA logic (logic di Enum/Service)
- [ ] Responsive: mobile-first, tabel overflow-x-auto, grid form adaptif, action bar stack di mobile
- [ ] Semua action button punya wire:key unik + loading state (target prop pada x-loading-button/x-cancel-button)
- [ ] File (jika ada): async via Job/worker + FileStorageService (tanpa duplikasi path builder)
- [ ] Export Excel & PDF (jika relevan) + permission-nya
- [ ] Tidak ada N+1, list paginated, search/filter server-side
- [ ] Test dasar lulus + Pint bersih
- [ ] Tidak ada dead code / field mati / duplicate logic
- [ ] URL aman: ID terenkripsi, tidak ada ID mentah di URL/link

# Dilarang
- Menyisakan field mati / logic di Blade / hardcode role-permission di view
- Membuat duplicate component/service/trait
- Membuat fitur tanpa permission + Policy
- Menyimpan file secara sinkron di request (harus lewat Job/worker)
- Membuat migration `add_*` baru untuk tabel yang schema-nya masih boleh diubah
- Membuat migration dengan timestamp yang SAMA dengan migration lain (konflik di tabel `migrations` Laravel → salah satu migration bisa ter-skip & tabel tidak dibuat). WAJIB gunakan timestamp unik (increment detik/menit jika perlu).
- Menggunakan ID mentah (angka) di URL untuk model yang punya HasEncryptedRouteKey
- Membuat action button tanpa wire:key dan loading state
- Menggunakan `wire:target=` pada `<x-loading-button>`/`<x-cancel-button>` — gunakan `target=` (Blade prop). `wire:target=` hanya untuk plain `<button>`.
- Memakai modal untuk form kompleks (nested/repeater/upload file) -- gunakan full-page form
- Membuat komponen Blade baru jika fungsi sudah ada di inventaris komponen
- Hardcode spacing/warna/typography yang inkonsisten dengan design system template
- Membuat tabel tanpa overflow-x-auto (horizontal scroll di mobile)
- Membuat form grid tanpa breakpoint responsif (harus adaptif 1/2/3 kolom)
- Membuat full-page form dengan `max-w-*` atau `mx-auto` — gunakan `w-full` (form full-width)
- Membuat action bar di dalam main card dengan `bg-gray-50` — gunakan card terpisah dengan `border`
- Menggunakan `dark:bg-gray-900` untuk input field — WAJIB `dark:bg-gray-700` (konsisten dengan searchable-select & textarea)
- Membuat action button di tabel tanpa dual-icon loading pattern (icon + spinner) — user tidak melihat feedback loading

# Modal Form Pattern (WAJIB ikuti pola bos-apps)
Untuk modal form (tambah/edit data), JANGAN pakai `<x-modal>` component (yang punya `x-transition`).
Gunakan pola `@if($showModal)` + `@entangle` + custom HTML seperti bos-apps:

```blade
@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeModal()"></div>
            <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ $editMode ? 'Edit' : 'Tambah' }}
                        </h3>
                        {{-- form fields --}}
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <x-loading-button type="submit" target="save" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                            {{ $editMode ? 'Update' : 'Simpan' }}
                        </x-loading-button>
                        <x-cancel-button wire:click="closeModal" target="closeModal" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
```

Alasan:
- `@if($showModal)` = modal unload **instant** saat `showModal=false` (di re-render yang sama dengan server response). Spinner & modal hilang bersamaan — tidak ada delay transition.
- `<x-modal>` component pakai `x-transition` (200ms fade) → spinner hilang duluan sebelum modal tertutup (UX inconsistency).
- Action bar di dalam modal card dengan `bg-gray-50 dark:bg-gray-900` + `sm:flex-row-reverse` (Submit kanan, Cancel kiri).
- Backdrop click → `$wire.closeModal()` (Livewire method, bukan Alpine state).

Untuk confirmation modal (delete): pakai `<x-delete-modal>` dengan `confirmMethod` prop (TIDAK perlu custom slot).
```blade
<x-delete-modal wire:model="showDeleteModal" title="Hapus Data" message="Yakin ingin menghapus?" confirmMethod="delete" />
```
Button confirm di dalam `<x-delete-modal>` sudah pakai plain `<button>` dengan `wire:loading` spinner (pola bos-apps).

# Action Modal Pattern (approve/reject/return dll) — WAJIB pakai plain `<button>`, BUKAN `<x-loading-button>`
Untuk modal yang berisi konfirmasi aksi (approve, reject, return, close, dll), JANGAN pakai `<x-loading-button>` component.
Gunakan **plain `<button>`** dengan `wire:loading` directive langsung pada spinner SVG (pola bos-apps `<x-action-modal>`).

Alasan: `<x-loading-button>` pakai `wire:loading.class.remove="hidden"` + `wire:loading.class.add="inline-flex"` yang TIDAK reliable
di Livewire v3 untuk elemen di dalam `@if` block. Plain `<button>` dengan `wire:loading` (default hide/show) lebih simple & reliable.

## Aturan WAJIB untuk Action Modal (approve/reject/return):
1. **JANGAN pakai `x-data` atau `@entangle`** di modal div. Pakai plain `<div class="fixed inset-0...">` saja.
   `@entangle` mengganggu `wire:loading` karena trigger Alpine reactivity saat property change.
2. **Backdrop click**: pakai `@click="$wire.set('showXxxModal', false)"` (bukan `$wire.showXxxModal = false`).
3. **Cancel button WAJIB punya `target` prop**: `<x-cancel-button ... target="closeXxxModal" ... />`.
   Tanpa `target`, cancel-button render plain button tanpa loading state.
4. **Action button**: plain `<button>` dengan `wire:loading` (bukan `<x-loading-button>`).

Pattern lengkap (copy-paste, ganti method name & warna):
```html
@if($showApproveModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showApproveModal', false)"></div>
            <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    {{-- modal content --}}
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="approveReview"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                        wire:target="approveReview">
                        <svg wire:loading wire:target="approveReview" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Setujui
                    </button>
                    <x-cancel-button wire:click="$set('showApproveModal', false)" target="closeApproveModal" wire:key="btn-approve-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                </div>
            </div>
        </div>
    </div>
@endif
```

Untuk form submit (reject dengan alasan wajib): bungkus dalam `<form wire:submit="rejectReview">` dan pakai `type="submit"` di button.
Warna button: `bg-emerald-600` (approve/success), `bg-red-600` (reject/danger), `bg-blue-600` (info/primary).
