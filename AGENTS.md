# Simona - Project Guide

## Tech Stack
- Laravel 12 + Livewire 4 + Spatie Permission/Activitylog + Reverb + Maatwebsite Excel + DomPDF
- Database: PostgreSQL (`db_simona`)
- PHP 8.2+

## Architecture Patterns
- **Livewire-first**: Full-page Livewire components for each module, mounted via `Route::view()` or route-model-binding closures.
- **Service layer**: Each entity has a `*Service` class for business logic (filtering, CRUD, workflow transitions).
- **Policy + Gate registration**: Policies registered explicitly in `AppServiceProvider::boot()` via `Gate::policy()`. Super admin bypasses via `Gate::before`.
- **Enums**: Status/type fields use PHP 8.1 backed enums in `app/Enums/`, with `label()`, `color()`, `badgeClass()`, `values()` methods.
- **Encrypted route keys**: Models use `HasEncryptedRouteKey` trait for non-enumerable IDs in URLs.
- **Activity logging**: Models use `Spatie\Activitylog\Traits\LogsActivity` with `getActivitylogOptions()` defining log fields.
- **File storage**: `FileStorageService` handles temp→final file flow. Uploads go to temp disk, then a queued Job moves them to the final disk via `moveFromTemp()`.
- **Dynamic LIKE operator**: `HasDynamicLike` trait provides `getLikeOperator()` for cross-DB LIKE/ILIKE compatibility.

## Conventions
- **Permissions**: Named `{entity}_{action}` (e.g. `cabang_view`, `pengadaan_approve`, `pembayaran_approve`). Defined in `PermissionSeeder`, grouped in `RolePermissionService::buildPermissionGroups()`.
- **Roles**: `super admin` (bypass all), `admin pusat` (all permissions + access_all_cabang), `admin cabang` (own cabang only), `staff cabang` (basic read).
- **Menu**: Built in `HasMenuItems` trait, permission-gated via `Gate::allows()`.
- **Migrations**: Use `2024_01_01_*` prefix for core, `2025_12_06_*` for pengadaan module.
- **Table naming**: Laravel auto-pluralizes — for non-English names that the inflector mishandles, set `protected $table` explicitly on the model.
- **Activitylog migration**: Must be published via `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`.

## Build/Test Commands
```bash
php artisan migrate:fresh --seed    # Reset DB + seed
php artisan serve --port=8000       # Dev server
php artisan view:cache              # Verify all blade views compile
php -l <file>                       # Lint a PHP file
```

## Seed Accounts
- `superadmin@app.com` / `password` — super admin
- `admin@app.com` / `password` — admin
- `user@app.com` / `password` — regular user

## Modules

### Master Data
Entities: `Cabang` (branch), `Vendor` (supplier).

### Pengadaan Aset Module
Entities: `Pengadaan` (procurement), `PengadaanItem` (procurement line item), `PengadaanEvidence` (procurement documents), `Invoice`, `InvoicePayment`.

### Workflows
- **Pengadaan approval**: `pending` → `approved`/`rejected` (via `pengadaan_approve` permission)
- **Payment approval**: `pending` → `approved`/`rejected` (via `pembayaran_approve` permission)
- **Evidence files**: Uploaded via Livewire `WithFileUploads`, stored to temp, then queued Job moves to final disk and updates status.

### Routes
- `master-data/cabangs` — Cabang management (modal-based CRUD)
- `master-data/vendors` — Vendor management (modal-based CRUD)
- `pengadaan` — Pengadaan list; `/create`, `/{pengadaan}/edit`, `/{pengadaan}` for form/detail

### Data Scoping
- `access_all_cabang` permission controls cross-cabang data visibility (COE/Pusat vs Cabang).
- `User::applyCabangScope()` is the single source of truth for cabang-scoped queries in Service/Export layers.
