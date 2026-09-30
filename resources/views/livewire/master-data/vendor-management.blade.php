<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari vendor..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        <x-filter-popover :filters="['statusFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <x-searchable-select
                    wire:model.live="statusFilter"
                    :options="$this->statusOptions"
                    placeholder="Semua Status"
                    searchPlaceholder="Cari status..."
                />
            </div>
        </x-filter-popover>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            @can('vendor_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" wire:key="btn-export-excel" variant="success" size="md" loadingText="Exporting..." title="Export Excel">
                    <x-slot:icon><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
                    Excel
                </x-loading-button>
            @endcan
            @can('vendor_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" wire:key="btn-export-pdf" variant="danger" size="md" loadingText="Exporting..." title="Export PDF">
                    <x-slot:icon><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></x-slot:icon>
                    PDF
                </x-loading-button>
            @endcan
            @can('vendor_create')
                <x-loading-button wire:click="create" target="create" wire:key="btn-create" variant="primary" size="md" loadingText="Memuat..." class="flex-1 sm:flex-none">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
                    Tambah Vendor
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Klaster</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NPWP</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($vendors as $vendor)
                        <tr wire:key="vendor-{{ $vendor->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-xs">
                                            {{ substr($vendor->code, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $vendor->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $vendor->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400">
                                    {{ $vendor->klaster->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $vendor->contact_person ?? '-' }}</div>
                                @if($vendor->phone)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $vendor->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $vendor->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $vendor->npwp ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @can('vendor_update')
                                    <x-toggle-switch wire:click="toggleStatus({{ $vendor->id }})"
                                        :active="$vendor->status->value === 'aktif'"
                                        target="toggleStatus({{ $vendor->id }})"
                                        wire:key="toggle-status-{{ $vendor->id }}"
                                        title="Aktifkan/Nonaktifkan" />
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $vendor->status->badgeClass() }}">
                                        {{ $vendor->status->label() }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('vendor_update')
                                        <button wire:click="edit({{ $vendor->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="edit({{ $vendor->id }})"
                                            wire:key="btn-edit-{{ $vendor->id }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 disabled:opacity-50"
                                            title="Edit">
                                            <svg wire:loading.class="hidden" wire:target="edit({{ $vendor->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <svg wire:loading wire:target="edit({{ $vendor->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endcan
                                    @can('vendor_delete')
                                        @if($vendor->pengadaans_count === 0)
                                        <button wire:click="confirmDelete({{ $vendor->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="confirmDelete({{ $vendor->id }})"
                                            wire:key="btn-delete-{{ $vendor->id }}"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                                            title="Hapus">
                                            <svg wire:loading.class="hidden" wire:target="confirmDelete({{ $vendor->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <svg wire:loading wire:target="confirmDelete({{ $vendor->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada vendor ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $vendors->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeModal()"></div>

                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editMode ? 'Edit Vendor' : 'Tambah Vendor' }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="code" value="Kode Vendor" :required="true" />
                                    <x-text-input wire:model="code" id="code" type="text" class="mt-1 block w-full" placeholder="Mis. VDR-001" />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="name" value="Nama Vendor" :required="true" />
                                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="Mis. PT Sumber Makmur" />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="klaster_id" value="Klaster" :required="true" />
                                    <x-searchable-select wire:model="klaster_id" :options="$this->klasterOptions" placeholder="Pilih klaster" searchPlaceholder="Cari klaster..." />
                                    <x-input-error :messages="$errors->get('klaster_id')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="kategori_item_ids" value="Kategori Item" />
                                    <x-multi-searchable-select wire:model="kategori_item_ids" :options="$this->kategoriItemOptions" placeholder="Pilih kategori item" searchPlaceholder="Cari kategori item..." wire:key="vendor-kategori-items" />
                                    <x-input-error :messages="$errors->get('kategori_item_ids')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="address" value="Alamat" />
                                    <x-text-input wire:model="address" id="address" type="text" class="mt-1 block w-full" placeholder="Alamat lengkap" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="contact_person" value="Nama Kontak" />
                                    <x-text-input wire:model="contact_person" id="contact_person" type="text" class="mt-1 block w-full" placeholder="Nama PIC vendor" />
                                    <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Telepon" />
                                    <x-text-input wire:model="phone" id="phone" type="text" class="mt-1 block w-full" placeholder="0812xxxxxxx" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" placeholder="vendor@email.com" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="npwp" value="NPWP" />
                                    <x-npwp-input wire-model="npwp" id="npwp" />
                                    <x-input-error :messages="$errors->get('npwp')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="status" value="Status" :required="true" />
                                    <x-searchable-select wire:model.live="status" :options="$this->statusOptions" placeholder="Pilih status" />
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" wire:key="btn-save" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                                {{ $editMode ? 'Update' : 'Simpan' }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeModal" target="closeModal" wire:key="btn-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <x-delete-modal
        :show="$showDeleteModal"
        wire:model="showDeleteModal"
        title="Hapus Vendor"
        message="Apakah Anda yakin ingin menghapus vendor"
        :itemName="$deletingVendorName"
        confirmMethod="delete"
    />
</div>
