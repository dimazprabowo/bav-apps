@props(['alat'])

<div class="px-4 py-5 sm:p-6">
    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informasi Umum</h4>
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Merk/Type</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->merk_type ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Serial Number</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->serial_number ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Kode Inventaris</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->kode_inventaris ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Cabang</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->cabang?->name ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Lokasi</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->lokasi ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Kepemilikan</dt>
            <dd class="mt-1"><span class="px-2 py-1 text-xs font-medium rounded-full {{ $alat->status_kepemilikan->badgeClass() }}">{{ $alat->status_kepemilikan->label() }}</span></dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Status</dt>
            <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->is_active ? 'Aktif' : 'Tidak Aktif' }}</dd>
        </div>
        @if($alat->description)
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-gray-500 dark:text-gray-400">Deskripsi</dt>
                <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->description }}</dd>
            </div>
        @endif
    </dl>
</div>
