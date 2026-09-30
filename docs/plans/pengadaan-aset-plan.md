# Plan: Modul Pengadaan Aset (Vendor – Biaya – Invoice – Pembayaran – Evidence)

> **Status:** Disetujui untuk eksekusi (desain final). Belum ada kode yang dibuat.
> **Dibuat:** 2026-09-07
> **Untuk siapa:** Dokumen ini ditulis agar bisa dieksekusi oleh AI agent manapun (Devin, GLM, dll)
> tanpa perlu konteks percakapan sebelumnya. Semua konvensi proyek dirujuk ke `AGENTS.md` di root
> repo — WAJIB dibaca dulu sebelum implementasi apa pun.

---

## 0. Cara Pakai Dokumen Ini

1. Baca `AGENTS.md` di root repo dulu — itu adalah aturan wajib arsitektur proyek (Livewire-first,
   Service layer, Policy, Enum, permission `{entity}_{action}`, dst). Dokumen ini **tidak
   mengulang** semua aturan itu, hanya menambahkan desain spesifik untuk modul baru ini.
2. Ikuti **Protokol Kerja AI** di `AGENTS.md`: ANALISIS → RENCANA → EKSEKUSI → VERIFIKASI. Dokumen
   ini adalah hasil dari tahap ANALISIS + RENCANA. Eksekusi dilakukan **per fase** (lihat §8),
   jangan sekaligus.
3. Jika ragu / ada ambiguitas besar saat eksekusi, **jangan berasumsi** — tanyakan ke user, atau
   catat asumsi yang diambil di bagian akhir file ini (lihat §9 Change Log) supaya agent lain tahu.
4. Setelah setiap fase selesai, update checklist di §8 (centang `[x]`) dan tambahkan catatan di §9.

---

## 1. Latar Belakang & Tujuan Bisnis

Aplikasi ini (bav-apps, BKI) sudah punya modul **Alat** (equipment monitoring: kondisi, kalibrasi,
peminjaman) di bawah `Cabang`. Modul baru ini menjawab kebutuhan **finansial-procurement** yang
belum ada:

- Berapa **total biaya aset** yang dibeli dari tiap vendor?
- Berapa yang **sudah** ditagih (invoice) vs **belum** ditagih vendor?
- Berapa yang **sudah dibayar (lunas)**, **dibayar sebagian**, atau **belum dibayar**?
- Ada bukti dokumen (PO/kontrak, invoice, bukti transfer) di tiap tahap?
- Siapa yang approve pengadaan & pembayaran (accountability / audit trail)?

## 2. Keputusan Desain (Sudah Difinalkan — Jangan Diubah Tanpa Konfirmasi User)

| Keputusan | Pilihan Final | Alasan |
|---|---|---|
| Relasi Pengadaan ↔ Aset | 1 Pengadaan bisa berisi **banyak item aset** (fleksibel) | Vendor sering menagih 1 PO untuk banyak unit sekaligus |
| Skema pembayaran | **Termin/bertahap** (banyak `InvoicePayment` per `Invoice`) | Pembayaran ke vendor sering dicicil (DP + pelunasan) |
| Cakupan modul | **Modul baru "Pengadaan Aset"**, terpisah dari `Alat`, dengan link **opsional** (`alat_id` nullable) ke `Alat` existing | `Alat` adalah domain operasional (kalibrasi/peminjaman); jangan dicampur dengan domain finansial-procurement. Tidak semua aset finansial adalah `Alat` operasional (mis. furniture) |
| Approval workflow | **Ada 2 titik approval**: (1) approval `Pengadaan`, (2) approval `InvoicePayment` | Meniru pola `alat_review` yang sudah terbukti. Approval Pengadaan = validasi PO sebelum masuk hitungan biaya. Approval Payment = kontrol finansial sebelum dianggap "sudah dibayar" |
| Penamaan entity | **"Pengadaan"** (bukan "Purchase Order") | Konsisten dengan istilah Bahasa Indonesia yang sudah dipakai di seluruh app (Alat, Cabang, LogBook) |

## 3. Entity Relationship Diagram

```
Vendor ──1:N──▶ Pengadaan ──1:N──▶ PengadaanItem ──N:1──▶ Alat (nullable, existing)
                    │                                        Cabang (existing)
                    ├──1:N──▶ PengadaanEvidence (dok. PO/kontrak, multi-file)
                    │
                    └──1:N──▶ Invoice ──1:N──▶ InvoicePayment
                                (1 file/invoice)   (1 file/payment, termin)
```

| Relasi | Kardinalitas | Alasan |
|---|---|---|
| Vendor → Pengadaan | 1:N | 1 vendor bisa disupply berkali-kali |
| Pengadaan → PengadaanItem | 1:N | 1 PO/kontrak bisa berisi banyak aset sekaligus |
| PengadaanItem → Alat | N:1 (nullable) | Link opsional ke aset operasional; jika tidak relevan, cukup nama aset manual |
| Pengadaan → Invoice | 1:N | Vendor bisa menagih bertahap (progress billing) |
| Invoice → InvoicePayment | 1:N | Pembayaran bisa dicicil/termin |

## 4. Skema Tabel (Detail Kolom)

### 4.1 `vendors`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| code | string, unique | Uppercase, dinormalisasi di `VendorService` |
| name | string | |
| contact_person | string, nullable | |
| phone | string, nullable | |
| email | string, nullable | |
| address | text, nullable | |
| npwp | string, nullable | |
| status | enum `VendorStatus` (aktif/nonaktif) | |
| created_at, updated_at, deleted_at | | Soft delete |

Model: `App\Models\Vendor` — `HasEncryptedRouteKey`, `HasFactory`, `LogsActivity`, `SoftDeletes`.

### 4.2 `pengadaans` (header)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| no_pengadaan | string, unique | Uppercase |
| vendor_id | FK → vendors | |
| cabang_id | FK → cabangs, nullable | Untuk data-scoping (reuse pola `access_all_cabang` seperti `AlatService::applyCabangScope`) |
| tanggal_pengadaan | date | |
| total_biaya | decimal(15,2) | **Dihitung otomatis** di Service dari `SUM(pengadaan_items.subtotal)` — JANGAN jadi input manual, cegah inkonsistensi |
| status_approval | enum `PengadaanApprovalStatus` (pending/approved/rejected) | |
| approved_by | FK → users, nullable | |
| approved_at | datetime, nullable | |
| rejection_reason | text, nullable | |
| catatan | text, nullable | |
| created_at, updated_at, deleted_at | | |

Model: `App\Models\Pengadaan` — `HasEncryptedRouteKey`, `HasFactory`, `LogsActivity`, `SoftDeletes`.

### 4.3 `pengadaan_items`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengadaan_id | FK → pengadaans, `cascadeOnDelete()` | |
| alat_id | FK → alats, nullable, `nullOnDelete()` | Link opsional |
| nama_item | string | **Selalu diisi** (denormalized — riwayat tetap utuh walau `Alat` dihapus/rename) |
| kategori_item | string, nullable | Bebas teks untuk saat ini |
| qty | integer | |
| satuan_id | FK → satuans, `restrictOnDelete()` | Master data satuan |
| harga_satuan | decimal(15,2) | |
| subtotal | decimal(15,2) | `qty * harga_satuan`, dihitung di Service saat save |
| created_at, updated_at | | Tidak perlu soft delete (child dari Pengadaan) |

Model: `App\Models\PengadaanItem` — tanpa `HasEncryptedRouteKey` (tidak muncul sendiri di URL,
selalu diakses via parent `Pengadaan`).

### 4.4 `pengadaan_evidences`

Pola identik `AlatEvidence` (lihat `app/Models/AlatEvidence.php` sebagai referensi):

| Kolom | Tipe |
|---|---|
| id, pengadaan_id (FK `cascadeOnDelete`), name, file_path, file_name, file_size, file_status, file_error, file_processed_at, created_at, updated_at | |

Model: `App\Models\PengadaanEvidence`.

### 4.5 `invoices`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengadaan_id | FK → pengadaans, `cascadeOnDelete()` | |
| no_invoice | string | Dari vendor |
| tanggal_invoice | date | |
| jumlah | decimal(15,2) | Nilai tagihan (bisa < `total_biaya` pengadaan jika progress billing) |
| jatuh_tempo | date, nullable | Untuk reminder |
| catatan | text, nullable | |
| file_path, file_name, file_size, file_status, file_error, file_processed_at | | Pola single-file seperti `AlatKalibrasi` |
| created_at, updated_at | | |

Model: `App\Models\Invoice`.

### 4.6 `invoice_payments`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| invoice_id | FK → invoices, `cascadeOnDelete()` | |
| tanggal_bayar | date | |
| jumlah_bayar | decimal(15,2) | |
| metode_bayar | string, nullable | Transfer/Cek/dll |
| status_approval | enum `PaymentApprovalStatus` (pending/approved/rejected) | |
| approved_by | FK → users, nullable | |
| approved_at | datetime, nullable | |
| rejection_reason | text, nullable | |
| catatan | text, nullable | |
| file_path, file_name, file_size, file_status, file_error, file_processed_at | | Bukti transfer |
| created_at, updated_at | | |

Model: `App\Models\InvoicePayment`.

> **Aturan penting:** hanya payment berstatus `approved` yang dihitung ke `SUM(payments)` untuk
> derived status invoice. Payment `pending`/`rejected` TIDAK mengurangi outstanding.

## 5. Status Derived (Accessor, BUKAN Kolom Mentah)

Mengikuti pola `Alat::getLatestKalibrasiAttribute()` / `AlatStatusKalibrasi` yang sudah ada.

**`Invoice::getStatusPembayaranAttribute()`**
```php
$totalDibayar = $this->payments()->where('status_approval', PaymentApprovalStatus::Approved)->sum('jumlah_bayar');
if ($totalDibayar >= $this->jumlah) return 'Lunas';
if ($totalDibayar > 0) return 'Sebagian';
return 'Belum Dibayar';
```
Untuk performa, saat dipakai di list/index gunakan `withSum('payments as total_dibayar', 'jumlah_bayar')`
dengan constraint `approved` (jangan N+1 — lihat §7 catatan performa).

**`Pengadaan::getStatusInvoiceAttribute()`**
```php
$totalInvoice = $this->invoices()->sum('jumlah');
if ($totalInvoice == 0) return 'Belum Ditagih';
if ($totalInvoice < $this->total_biaya) return 'Ditagih Sebagian';
return 'Sudah Ditagih Penuh';
```

**`Pengadaan::getStatusPembayaranAttribute()`** — agregat dari status pembayaran semua invoice
miliknya → `'Belum Dibayar' | 'Dibayar Sebagian' | 'Lunas'`.

Buat enum baru untuk label/warna badge ini (jangan hardcode di Blade), mis.
`App\Enums\StatusInvoicePengadaan` dan `App\Enums\StatusPembayaranPengadaan`, masing-masing dengan
method `label()` dan `badgeClass()` sesuai pola enum lain di `app/Enums/`.

## 6. Approval Workflow (State Diagram)

**Pengadaan:**
```
[Pending] --approve (permission: pengadaan_approve)--> [Approved]
[Pending] --reject  (permission: pengadaan_approve)--> [Rejected]  (wajib isi rejection_reason)
```
Hanya `Pengadaan` berstatus **Approved** yang dihitung ke total biaya aset di dashboard (data
belum-fix tidak mengotori laporan).

**InvoicePayment:**
```
[Pending] --approve (permission: pembayaran_approve)--> [Approved]  → masuk hitungan "sudah dibayar"
[Pending] --reject  (permission: pembayaran_approve)--> [Rejected]  (wajib isi rejection_reason)
```

Kedua permission approval **HARUS berbeda** dari permission `update` biasa, sesuai rule
"Policy untuk Aksi Transisi Status" di `AGENTS.md`:
> Method Policy WAJIB (1) cek permission YANG SAMA dengan yang dipakai `@can(...)` di Blade untuk
> menampilkan tombolnya, (2) cek status saat ini valid untuk transisi tersebut.

Contoh implementasi `PengadaanPolicy::approve()`:
```php
public function approve(User $user, Pengadaan $pengadaan): bool
{
    return $user->can('pengadaan_approve') && $pengadaan->status_approval === PengadaanApprovalStatus::Pending;
}
```

## 7. Permission (Konvensi `{entity}_{action}`)

Tambahkan di `Database\Seeders\PermissionSeeder` (lihat struktur existing untuk `alat_*`,
`cabang_*`, `logbook_*` sebagai referensi format):

```php
// Vendor
'vendor_view',
'vendor_create',
'vendor_update',
'vendor_delete',
'vendor_export_excel',
'vendor_export_pdf',

// Pengadaan (mencakup CRUD PengadaanItem, Invoice, InvoicePayment biasa —
// ikut pola "History/Recurring Records" seperti Kalibrasi di bawah Alat)
'pengadaan_view',
'pengadaan_create',
'pengadaan_update',
'pengadaan_delete',
'pengadaan_approve',
'pengadaan_export_excel',
'pengadaan_export_pdf',

// Approval pembayaran (dedicated, karena aksi finansial sensitif —
// TIDAK memakai pengadaan_update)
'pembayaran_approve',
```

> **Catatan penting:** `Invoice` & `InvoicePayment` **TIDAK punya permission CRUD sendiri**. CRUD
> biasa (tambah/edit/hapus invoice, tambah payment) dikelola di halaman Detail Pengadaan dan cukup
> memakai permission `pengadaan_update` — ini konsisten dengan rule "History/Recurring Records
> Pattern" di `AGENTS.md` (poin 10: "Permission: pakai permission `update` dari model utama").
> Hanya **approve payment** yang dedicated karena itu transisi status finansial kritis.

Tambahkan grouping baru di `App\Services\RolePermissionService::buildPermissionGroups()`:
grup **"Pengadaan Aset"** berisi permission `vendor_*` dan `pengadaan_*` + `pembayaran_approve`.

## 8. Policy

- `App\Policies\VendorPolicy` — standar (viewAny/view/create/update/delete/exportExcel/exportPdf),
  daftarkan di `AppServiceProvider::boot()` via `Gate::policy(Vendor::class, VendorPolicy::class)`.
- `App\Policies\PengadaanPolicy` — standar + method `approve()` (lihat §6 contoh) dan `reject()`
  (permission sama, validasi status sama).
- **Tidak perlu Policy terpisah** untuk `Invoice`/`InvoicePayment` — otorisasi CRUD-nya cukup lewat
  `PengadaanPolicy::update()` (karena dikelola di Detail Pengadaan). Untuk approve payment, buat
  method khusus `PengadaanPolicy::approvePayment()` (permission `pembayaran_approve` + validasi
  status payment `Pending`).

## 9. Menu & Navigasi

Tambahkan di `App\Livewire\Traits\HasMenuItems` (lihat pola existing untuk `Alat`/`Cabang` di file
yang sama sebagai referensi struktur array menu):

```
Master Data
  ├── Cabang (existing)
  ├── Alat (existing)
  └── Vendor (BARU)   -> route: master-data.vendors, gate: vendor_view

Pengadaan Aset (BARU, grup menu baru)
  └── Pengadaan (list + detail: item, invoice, payment, evidence)
      -> route: pengadaan.index, gate: pengadaan_view
```

## 10. Form Strategy per Halaman

| Halaman | Pola | Alasan (sesuai rule Form Strategy di `AGENTS.md`) |
|---|---|---|
| Vendor CRUD | **Modal** (`@if($showModal)` pattern) | ≤ 7 field sederhana, tanpa nested/file |
| Pengadaan Create/Edit | **Full-page** | Nested item aset (repeater) + upload evidence → wajib full-page |
| Tambah/Edit Invoice | **Modal**, dikelola di dalam Detail Pengadaan | Field sedikit (no invoice, tanggal, jumlah, jatuh tempo, catatan) + 1 upload file — dikelola sebagai sub-record seperti Kalibrasi di bawah Alat |
| Tambah Payment | **Modal**, dikelola di dalam Detail Invoice/Pengadaan | Sama alasan seperti Invoice |
| Approve/Reject Pengadaan & Payment | **Action Modal** (pola `x-action-modal`, lihat `AGENTS.md` §"Action Modal Pattern") | Konfirmasi aksi transisi status |

## 11. File / Evidence Strategy (Reuse Pola Existing — JANGAN Bikin Baru)

| Level | Pola | Model referensi yang ditiru |
|---|---|---|
| Pengadaan | Multi-file, `HasMany` | `AlatEvidence` + `App\Jobs\ProcessAlatEvidence` |
| Invoice | Single-file di kolom record | `AlatKalibrasi` + `App\Jobs\ProcessAlatKalibrasi` |
| InvoicePayment | Single-file di kolom record | Sama seperti Invoice |

Semua WAJIB lewat `App\Services\FileStorageService` (jangan copy-paste logic path builder) +
Job async (`ShouldQueue`, `$tries = 3`, `$timeout = 120`, ada method `failed()`). Path final
konvensi: `{app_env}/pengadaan-aset/{no-pengadaan-slug}/{jenis}_{slug}_{YmdHis}.{ext}`.

## 12. Dashboard & Reporting

Tambahkan method di `App\Services\PengadaanService`:

- `getDashboardStats()`: total biaya aset (dari Pengadaan `Approved`), total sudah ditagih vs
  belum ditagih, total sudah dibayar (lunas) vs outstanding (belum dibayar).
- Breakdown per Vendor (top vendor by spend).
- Breakdown per Cabang.
- Invoice mendekati jatuh tempo (reminder, mirip pola `AlatService::getDashboardStats()` bagian
  `expiring_soon` untuk kalibrasi).
- Export Excel/PDF laporan pengadaan (permission `pengadaan_export_excel`/`pengadaan_export_pdf`).

**Catatan performa (WAJIB, sesuai `AGENTS.md`):** semua query dashboard harus pakai
`withCount`/`withSum`, eager load, dan di-scope per cabang jika user tidak punya
`access_all_cabang` (reuse pola `AlatService::applyCabangScope()`). Jangan query di dalam loop
Blade.

## 13. Test Data Amount / Angka Uang

- Gunakan `decimal(15,2)` untuk semua kolom nominal (bukan `integer`/`float`) — presisi uang wajib
  eksak.
- Format tampilan di Blade pakai helper `number_format($amount, 0, ',', '.')` (format Rupiah,
  ikuti konvensi lokal Indonesia yang sudah dipakai di app existing — cek dulu apakah sudah ada
  helper serupa, misal `Str::currency()` atau helper custom, sebelum menulis baru).

---

## 14. Roadmap Eksekusi (4 Fase — WAJIB berurutan, jangan digabung)

Checklist ini diupdate oleh agent yang mengeksekusi. Centang `[x]` setelah fase selesai +
terverifikasi (`migrate:fresh --seed` sukses + Definition of Done `AGENTS.md` terpenuhi).

### [x] Fase A — Master Data Vendor

File yang dibuat:
- `database/migrations/xxxx_create_vendors_table.php`
- `app/Models/Vendor.php`
- `app/Enums/VendorStatus.php`
- `app/Services/VendorService.php`
- `app/Policies/VendorPolicy.php` + registrasi di `AppServiceProvider::boot()`
- `database/seeders/PermissionSeeder.php` (tambah `vendor_*`)
- `app/Services/RolePermissionService.php` (tambah grouping)
- `app/Livewire/Traits/HasMenuItems.php` (tambah menu Vendor)
- `app/Livewire/MasterData/VendorIndex.php` (atau namespace serupa, ikuti pola `Alat`/`Cabang`
  yang sudah ada — cek dulu namespace existing sebelum membuat)
- View Blade: index (tabel + modal CRUD + delete modal)
- `routes/web.php` (tambah route `master-data.vendors`)
- Feature test dasar (authorize gagal/berhasil, CRUD)

Verifikasi: `php artisan migrate:fresh --seed` sukses, CRUD Vendor jalan, permission-gated, Pint
bersih.

### [x] Fase B — Pengadaan + PengadaanItem + PengadaanEvidence

File yang dibuat:
- Migration `pengadaans`, `pengadaan_items`, `pengadaan_evidences`
- `app/Models/Pengadaan.php`, `PengadaanItem.php`, `PengadaanEvidence.php`
- `app/Enums/PengadaanApprovalStatus.php`
- `app/Jobs/ProcessPengadaanEvidence.php`
- `app/Services/PengadaanService.php` (create dengan nested items — hitung `subtotal` &
  `total_biaya` otomatis, approve/reject, evidence management)
- `app/Policies/PengadaanPolicy.php` (+ method `approve()`)
- Permission `pengadaan_*` (sudah didaftarkan sebagian di Fase A jika digabung seedernya — pastikan
  tidak duplikat)
- Livewire: full-page form (`PengadaanForm` dengan repeater item + upload evidence), Index, Detail
  page (`PengadaanDetail`)
- Menu: tambah grup "Pengadaan Aset"
- Feature test: create dengan nested item, approve/reject flow

Verifikasi: bisa buat pengadaan multi-item, link ke `Alat` opsional berfungsi, approve/reject
mengubah `total_biaya` dashboard dengan benar, `migrate:fresh --seed` sukses.

### [x] Fase C — Invoice + InvoicePayment

File yang dibuat:
- Migration `invoices`, `invoice_payments`
- `app/Models/Invoice.php`, `InvoicePayment.php` (+ accessor derived status §5)
- `app/Enums/PaymentApprovalStatus.php`, `StatusInvoicePengadaan.php`,
  `StatusPembayaranPengadaan.php`
- `app/Jobs/ProcessInvoiceFile.php`, `ProcessInvoicePaymentFile.php`
- Tambahkan method di `PengadaanService` (atau buat `InvoiceService` baru jika
  `PengadaanService` mulai terlalu besar — evaluasi saat implementasi): createInvoice,
  createPayment, approvePayment, rejectPayment
- `PengadaanPolicy::approvePayment()` (permission `pembayaran_approve`)
- Permission: tambah `pembayaran_approve` di seeder
- Livewire: modal CRUD Invoice & Payment di dalam `PengadaanDetail`, action modal approve/reject
  payment
- Feature test: derived status akurat (Lunas/Sebagian/Belum), approval payment flow

Verifikasi: status derived akurat untuk berbagai skenario (0 payment, partial, lunas, payment
rejected tidak dihitung), `migrate:fresh --seed` sukses.

### [x] Fase D — Dashboard, Reporting, Export, Reminder

File yang dibuat/diubah:
- `PengadaanService::getDashboardStats()`, `getSpendPerVendor()`, `getSpendPerCabang()`
- Filter/search server-side di Index Pengadaan (nama vendor, status approval, rentang tanggal,
  status pembayaran) via `HasDynamicLike`
- Export Excel (`app/Exports/PengadaanExport.php`, pola Maatwebsite Excel existing) & PDF
  (`resources/views/pdf/pengadaan.blade.php`, pola DomPDF existing)
- Permission export sudah ada dari Fase A/B, pastikan Policy method `exportExcel`/`exportPdf`
  terpasang
- Reminder invoice mendekati jatuh tempo (opsional — notifikasi in-app via `Notification` model
  yang sudah ada, atau cukup badge di dashboard)

Verifikasi: laporan akurat, tidak ada N+1 (cek dengan query log / Laravel Debugbar jika tersedia),
export Excel/PDF berhasil, filter server-side berfungsi.

---

## 15. Change Log / Catatan Eksekusi

> Agent yang mengeksekusi WAJIB menambah entri di sini setiap kali menyelesaikan satu fase, atau
> mengambil keputusan/asumsi yang tidak tercakup di dokumen ini.

- 2026-09-07 — Dokumen dibuat, desain difinalkan oleh Devin CLI bersama user. Belum ada fase yang
  dieksekusi.
- 2026-09-07 — **Fase A (Vendor) selesai dieksekusi & terverifikasi.** File yang dibuat/diubah:
  - Baru: `database/migrations/2025_12_05_000001_create_vendors_table.php`,
    `app/Models/Vendor.php`, `app/Enums/VendorStatus.php`, `app/Services/VendorService.php`,
    `app/Policies/VendorPolicy.php`, `app/Livewire/MasterData/VendorManagement.php`,
    `resources/views/livewire/master-data/vendor-management.blade.php`,
    `resources/views/master-data/vendors.blade.php`, `app/Exports/VendorExport.php`,
    `resources/views/exports/vendor-pdf.blade.php`, `database/seeders/VendorSeeder.php`,
    `database/factories/VendorFactory.php`, `tests/Feature/VendorManagementTest.php`.
  - Diubah: `app/Providers/AppServiceProvider.php` (registrasi `Gate::policy(Vendor::class, ...)`),
    `database/seeders/PermissionSeeder.php` (tambah `vendor_*`), `database/seeders/DatabaseSeeder.php`
    (tambah `VendorSeeder::class`), `app/Services/RolePermissionService.php` (grup "Vendor"),
    `app/Livewire/Traits/HasMenuItems.php` (menu Vendor di bawah Master Data), `routes/web.php`
    (route `master-data.vendors`).
  - **Asumsi/keputusan tambahan yang diambil saat eksekusi:**
    1. `VendorPolicy::delete()` untuk sementara **tidak** mengecek dependency ke `Pengadaan`
       (relasi belum ada — baru dibuat di Fase B). Method ini WAJIB diupdate di Fase B untuk
       menambahkan cek `$vendor->pengadaans()->exists()` sebelum izinkan delete, meniru pola
       `CabangPolicy::delete()`.
    2. Field `status` Vendor pakai value Bahasa Indonesia (`aktif`/`nonaktif`), bukan
       `active`/`inactive` seperti `CabangStatus` — mengikuti keputusan desain awal §4.1.
    3. `VendorSeeder` (2 vendor contoh) ditambahkan untuk kebutuhan testing/demo meski tidak
       eksplisit tercantum di checklist file Fase A — tidak mengubah keputusan desain manapun.
  - **Bug pre-existing yang ditemukan & diperbaiki (di luar scope Fase A, tapi memblokir
    verifikasi test dan berisiko crash saat fresh install produksi):** `routes/console.php`
    memanggil `SystemConfiguration::get()` secara **eager** sebagai argumen `dailyAt()` saat file
    route di-load — ini crash jika tabel `system_configurations` belum ada (fresh install sebelum
    migrate, atau test environment). Diperbaiki dengan membungkus try/catch + fallback default.
    Tidak mengubah behavior saat tabel sudah ada.
  - **Diagnostik environment (bukan bug kode):** ekstensi PHP `pdo_sqlite` tidak aktif di
    `php.ini` lokal developer — diaktifkan atas konfirmasi user agar `php artisan test` bisa
    jalan. Ini murni konfigurasi environment, bukan bagian dari repo.
  - **Catatan test suite lain (tidak diperbaiki, di luar scope):** 23 test lain
    (`AuthenticationTest`, `ProfileTest`, `RegistrationTest`, dll — sisa boilerplate Laravel
    Breeze) gagal karena mereferensikan `Livewire\Volt` yang tidak terinstall di app ini
    (app sudah Livewire-first murni, bukan Volt). Ini pre-existing technical debt, tidak
    berhubungan dengan modul Pengadaan Aset — perlu dibersihkan/diupdate terpisah oleh tim.
  - Verifikasi: `migrate:fresh --seed` sukses, `VendorManagementTest` 7/7 pass, Pint bersih untuk
    semua 17 file yang disentuh Fase A.
  - **Selanjutnya:** Fase B (Pengadaan + PengadaanItem + PengadaanEvidence) belum dikerjakan.
- 2026-09-07 — **Fase B (Pengadaan) selesai dieksekusi & terverifikasi.** File yang dibuat/diubah:
  - Baru: 3 migration (`pengadaans`, `pengadaan_items`, `pengadaan_evidence`), `app/Models/Pengadaan.php`,
    `PengadaanItem.php`, `PengadaanEvidence.php`, `app/Enums/PengadaanApprovalStatus.php`,
    `app/Jobs/ProcessPengadaanEvidence.php`, `app/Services/PengadaanService.php`,
    `app/Policies/PengadaanPolicy.php`, `app/Livewire/Pengadaan/PengadaanManagement.php`,
    `PengadaanForm.php`, `PengadaanDetail.php`, Blade views (`livewire/pengadaan/*`,
    `pengadaan/index|create|edit|show.blade.php`, `components/pengadaan-evidence-section.blade.php`,
    `components/pengadaan-item-section.blade.php`), `database/factories/PengadaanFactory.php`,
    `PengadaanItemFactory.php`, `tests/Feature/PengadaanManagementTest.php`.
  - Diubah: `app/Models/Vendor.php` (+`pengadaans()` relation, menyelesaikan TODO Fase A),
    `app/Policies/VendorPolicy.php` (delete() sekarang cek dependency Pengadaan, TODO Fase A selesai),
    `app/Services/VendorService.php` + blade Vendor (tambah `withCount('pengadaans')` + sembunyikan
    tombol delete jika ada pengadaan terkait — pola sama seperti Cabang↔Alat),
    `app/Providers/AppServiceProvider.php` (registrasi `Gate::policy(Pengadaan::class, ...)`),
    `database/seeders/PermissionSeeder.php` (tambah `pengadaan_view/create/update/delete/approve`),
    `app/Services/RolePermissionService.php` (grup "Vendor" DIGABUNG jadi grup "Pengadaan Aset"
    berisi vendor_* + pengadaan_*, sesuai desain §7 plan ini), `app/Livewire/Traits/HasMenuItems.php`
    (menu baru "Pengadaan Aset" top-level, terpisah dari submenu Master Data), `routes/web.php`
    (route `pengadaan.*`), `config/file_upload.php` (tambah field `pengadaan-evidence`),
    `resources/views/components/icon.blade.php` (tambah icon `shopping-cart`, extend bukan bikin
    komponen baru).
  - **Keputusan desain tambahan yang diambil saat eksekusi (tidak eksplisit di plan awal, dicatat
    di sini agar agent lain tahu):**
    1. Approve/reject dilakukan dari **index list** (`PengadaanManagement`), bukan dari halaman
       Detail — konsisten dengan pola `AlatManagement` yang sudah ada di codebase (bukan
       preferensi baru, hanya reuse konvensi existing).
    2. `PengadaanForm::update()` melakukan **full replace** pada items (hapus semua item lama,
       insert ulang) alih-alih diff/patch per item. Ini lebih simple & aman untuk MVP karena
       jumlah item per pengadaan biasanya kecil (bukan ribuan baris); jika nanti performa jadi
       isu, bisa dioptimasi ke strategi upsert per item.
    3. `PengadaanPolicy::delete()` **belum** mengecek dependency ke `Invoice` (karena `Invoice`
       belum ada — baru dibuat di Fase C). **WAJIB ditambahkan di Fase C**: cek
       `$pengadaan->invoices()->exists()` sebelum izinkan delete, meniru pola
       `AlatPolicy::delete()` (cek `logBookPeminjaman` aktif) dan `VendorPolicy::delete()`.
    4. `PengadaanPolicy::update()` **tidak** membatasi berdasarkan `status_approval` (Approved
       tetap bisa diedit oleh user berpermission) — meniru pola `AlatPolicy::update()` yang juga
       tidak mengunci status review. Jika bisnis butuh lock setelah approved (mis. karena sudah
       ada Invoice terkait di Fase C), pertimbangkan tambahkan validasi ini di Fase C bersamaan
       dengan dependency check delete.
  - Verifikasi: `migrate:fresh --seed` sukses, `PengadaanManagementTest` 8/8 pass,
    `VendorManagementTest` 7/7 pass (tidak ada regresi), Pint bersih untuk semua file yang disentuh
    Fase B.
  - **Selanjutnya:** Fase C (Invoice + InvoicePayment) belum dikerjakan. Jangan lupa 2 TODO di atas
    (poin 3 & 4) saat mengerjakan Fase C.
- 2026-09-07 — **Fase C (Invoice + InvoicePayment) selesai dieksekusi & terverifikasi.** File yang
  dibuat/diubah:
  - Baru: 2 migration (`invoices`, `invoice_payments`), `app/Models/Invoice.php`,
    `InvoicePayment.php`, `app/Enums/PaymentApprovalStatus.php`, `StatusInvoicePengadaan.php`,
    `StatusPembayaranPengadaan.php`, `app/Jobs/ProcessInvoiceFile.php`,
    `ProcessInvoicePaymentFile.php`, `app/Services/InvoiceService.php` (service TERPISAH dari
    `PengadaanService`, sesuai opsi yang diizinkan plan §14 Fase C), `database/factories/InvoiceFactory.php`,
    `InvoicePaymentFactory.php`, `tests/Feature/InvoicePaymentTest.php`.
  - Diubah: `app/Models/Pengadaan.php` (+relasi `invoices()`, +accessor derived
    `status_invoice`/`status_pembayaran`/`total_invoice`), `app/Policies/PengadaanPolicy.php`
    (`delete()` sekarang cek dependency Invoice — TODO Fase B selesai; +method
    `approvePayment(User, Pengadaan, InvoicePayment)` via array-policy authorize),
    `app/Livewire/Pengadaan/PengadaanDetail.php` (+CRUD Invoice & Payment lengkap, +approve/reject
    payment), `resources/views/livewire/pengadaan/pengadaan-detail.blade.php` (+section Invoice &
    Pembayaran, +5 modal baru), `app/Services/PengadaanService.php` (+`withCount('invoices')` di
    `getFiltered()`), `resources/views/livewire/pengadaan/pengadaan-management.blade.php`
    (+sembunyikan tombol delete jika `invoices_count > 0`), `database/seeders/PermissionSeeder.php`
    (+`pembayaran_approve`), `app/Services/RolePermissionService.php` (+label
    `pembayaran_approve` di grup "Pengadaan Aset"), `config/file_upload.php` (+field `invoice` dan
    `invoice-payment`).
  - **Keputusan desain tambahan yang diambil saat eksekusi:**
    1. `PengadaanPolicy::approvePayment()` diotorisasi via Laravel array-policy:
       `$this->authorize('approvePayment', [$pengadaan, $payment])` / Blade
       `@can('approvePayment', [$pengadaan, $payment])`. Laravel resolve Policy dari elemen
       pertama array (`Pengadaan::class` → `PengadaanPolicy`), lalu mengoper SEMUA elemen array
       sebagai argumen method setelah `$user`. Ini dipilih karena `InvoicePayment` tidak punya
       Policy class sendiri (sesuai desain plan §8 — CRUD lewat `PengadaanPolicy::update()`).
    2. `PengadaanPolicy::update()` **tetap tidak dikunci** berdasarkan ada/tidaknya Invoice
       (keputusan Fase B poin 4 dipertahankan — tidak ditambah lock, demi kesederhanaan sesuai
       arahan user "tetap sederhana"). Jika bisnis nantinya butuh lock, ini titik yang tepat untuk
       revisit.
    3. `InvoiceService` dibuat sebagai service terpisah (bukan method tambahan di
       `PengadaanService`) — `PengadaanService` sudah cukup besar (~230 baris) dan Invoice/Payment
       adalah concern finansial berbeda dari CRUD Pengadaan itu sendiri.
    4. Path storage invoice/payment memakai sub-folder terpisah dalam feature yang sama
       (`pengadaan-aset/invoice/...` dan `pengadaan-aset/payment/...`) alih-alih 1 feature key
       generik, supaya dokumen invoice vs bukti transfer tidak tercampur di 1 folder saat
       di-audit manual di storage.
  - Verifikasi: `migrate:fresh --seed` sukses, `InvoicePaymentTest` 8/8 pass, regresi
    `VendorManagementTest` 7/7 dan `PengadaanManagementTest` 8/8 tetap pass, Pint bersih.
  - **Status keseluruhan plan:** Fase A, B, C **SELESAI**. Sisa **Fase D** (Dashboard, Reporting,
    Export Excel/PDF, Reminder jatuh tempo invoice) — belum dikerjakan.
- 2026-09-09 — **Fase D (Dashboard, Reporting, Export, Reminder) selesai dieksekusi & terverifikasi.**
  File yang dibuat/diubah:
  - Baru: `app/Exports/PengadaanExport.php`, `resources/views/exports/pengadaan-pdf.blade.php`,
    `tests/Feature/PengadaanDashboardExportTest.php`.
  - Diubah: `app/Services/PengadaanService.php` (+`getDashboardStats()`, `getSpendPerVendor()`,
    `getSpendPerCabang()` — cabang-scoped seperti `AlatService`), `app/Livewire/Pages/Dashboard.php`
    (+inject `pengadaanStats`/`pengadaanPerVendor`/`pengadaanPerCabang` ke dashboard global, gated
    `pengadaan_view`), `resources/views/livewire/pages/dashboard-stats.blade.php` (+section
    "Pengadaan Aset": total biaya/belum ditagih/outstanding, invoice jatuh tempo ≤7 hari & overdue,
    top vendor by spend, breakdown per cabang), `app/Livewire/Pengadaan/PengadaanManagement.php`
    (+`exportExcel()`/`exportPdf()`), `resources/views/livewire/pengadaan/pengadaan-management.blade.php`
    (+tombol Export Excel/PDF), `app/Policies/PengadaanPolicy.php` (+`exportExcel()`/`exportPdf()`),
    `database/seeders/PermissionSeeder.php` (+`pengadaan_export_excel`/`pengadaan_export_pdf`),
    `app/Services/RolePermissionService.php` (+label export di grup "Pengadaan Aset").
  - **Keputusan desain tambahan yang diambil saat eksekusi:**
    1. **Reminder jatuh tempo** diimplementasikan sebagai **dashboard stat pasif** (`invoice_due_soon`
       ≤7 hari, `invoice_overdue`), **BUKAN** notifikasi email/scheduled command aktif seperti
       `AlatReminderService`. Plan §14 Fase D menandai reminder sebagai "opsional" — versi pasif
       ini dipilih untuk menjaga kesederhanaan (sesuai arahan user "tetap sederhana") sambil tetap
       memberi visibilitas. Jika nanti dibutuhkan reminder aktif (email/notifikasi), tinggal buat
       `PengadaanReminderService` + scheduled command meniru pola `alat:send-kalibrasi-reminders`
       di `routes/console.php` — struktur data (`invoices.jatuh_tempo`, derived `status_pembayaran`)
       sudah siap dipakai.
    2. Dashboard Pengadaan digabung ke **dashboard global** (`pages/dashboard.blade.php`, partial
       `dashboard-stats.blade.php`) mengikuti pola `alatStats`/`alatPerCabang` yang sudah ada,
       BUKAN halaman dashboard terpisah — konsisten dengan single-dashboard architecture yang
       sudah dipakai app ini.
    3. Export PDF Pengadaan pakai `perPage: 100000` pada `getFiltered()` (bukan query baru) agar
       filter yang sama (search/vendor/cabang/status) otomatis konsisten antara Excel & PDF &
       tampilan index — trade-off: query 1x lebih besar tapi menghindari duplikasi logic filter.
    4. Eager load `invoices.payments` ditambahkan di `PengadaanExport::query()` dan sebelum render
       PDF (`->load('invoices.payments')`) untuk mencegah N+1 saat accessor `status_invoice`/
       `status_pembayaran` diakses per baris (kedua accessor cek `relationLoaded('invoices')`).
  - Verifikasi: `migrate:fresh --seed` sukses, `PengadaanDashboardExportTest` 5/5 pass, regresi
    `VendorManagementTest` 7/7, `PengadaanManagementTest` 8/8, `InvoicePaymentTest` 8/8 tetap pass
    (total 28/28 test module Pengadaan Aset), Pint bersih di seluruh 203 file project (kecuali 1
    pre-existing issue tidak terkait di migration lama).
  - **STATUS AKHIR: Semua 4 fase plan (A/B/C/D) SELESAI.** Modul Pengadaan Aset (Vendor, Pengadaan,
    Item, Evidence, Invoice, Payment, Dashboard, Export) sudah lengkap dan production-ready sesuai
    Definition of Done `AGENTS.md`. Peluang penyempurnaan lanjutan (di luar scope plan awal, tidak
    wajib): reminder aktif via email (lihat poin 1 di atas), lock edit Pengadaan setelah ada
    Invoice (lihat catatan Fase C poin 2), upsert per-item saat update alih-alih full-replace
    (lihat catatan Fase B poin 2).
