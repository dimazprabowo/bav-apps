<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari no. pengadaan / vendor..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        <x-filter-popover :filters="['vendorFilter', 'cabangFilter', 'statusFilter']">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Vendor</label>
                    <x-searchable-select wire:model.live="vendorFilter" :options="$this->vendorOptions" placeholder="Semua Vendor" searchPlaceholder="Cari vendor..." />
                </div>
                @can('access_all_cabang')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cabang</label>
                        <x-searchable-select wire:model.live="cabangFilter" :options="$this->cabangOptions" placeholder="Semua Cabang" searchPlaceholder="Cari cabang..." />
                    </div>
                @endcan
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status Approval</label>
                    <x-searchable-select wire:model.live="statusFilter" :options="$this->statusOptions" placeholder="Semua Status" />
                </div>
            </div>
        </x-filter-popover>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            @can('pengadaan_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" wire:key="btn-export-excel" variant="success" size="md" loadingText="Exporting..." title="Export Excel">
                    <x-slot:icon><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
                    Excel
                </x-loading-button>
            @endcan
            @can('pengadaan_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" wire:key="btn-export-pdf" variant="danger" size="md" loadingText="Exporting..." title="Export PDF">
                    <x-slot:icon><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></x-slot:icon>
                    PDF
                </x-loading-button>
            @endcan
            @can('pengadaan_create')
                <x-loading-button wire:click="create" target="create" wire:key="btn-create" variant="primary" size="md" loadingText="Memuat..." class="flex-1 sm:flex-none">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
                    Tambah Pengadaan
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Pengadaan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Biaya</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pengadaans as $pengadaan)
                        <tr wire:key="pengadaan-{{ $pengadaan->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $pengadaan->no_pengadaan }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $pengadaan->items_count }} item, {{ $pengadaan->evidences_count }} evidence</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $pengadaan->vendor->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $pengadaan->cabang->name ?? 'Pusat' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($pengadaan->total_biaya, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $pengadaan->status_approval->badgeClass() }}">
                                    {{ $pengadaan->status_approval->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="show({{ $pengadaan->id }})"
                                        wire:loading.attr="disabled" wire:target="show({{ $pengadaan->id }})"
                                        wire:key="btn-show-{{ $pengadaan->id }}"
                                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 disabled:opacity-50"
                                        title="Detail">
                                        <svg wire:loading.class="hidden" wire:target="show({{ $pengadaan->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg wire:loading wire:target="show({{ $pengadaan->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </button>
                                    @can('approve', $pengadaan)
                                        <button wire:click="confirmApprove({{ $pengadaan->id }})"
                                            wire:loading.attr="disabled" wire:target="confirmApprove({{ $pengadaan->id }})"
                                            wire:key="btn-approve-{{ $pengadaan->id }}"
                                            class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 disabled:opacity-50"
                                            title="Setujui">
                                            <svg wire:loading.class="hidden" wire:target="confirmApprove({{ $pengadaan->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <svg wire:loading wire:target="confirmApprove({{ $pengadaan->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                        <button wire:click="confirmReject({{ $pengadaan->id }})"
                                            wire:loading.attr="disabled" wire:target="confirmReject({{ $pengadaan->id }})"
                                            wire:key="btn-reject-{{ $pengadaan->id }}"
                                            class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 disabled:opacity-50"
                                            title="Tolak">
                                            <svg wire:loading.class="hidden" wire:target="confirmReject({{ $pengadaan->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <svg wire:loading wire:target="confirmReject({{ $pengadaan->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endcan
                                    @can('update', $pengadaan)
                                        <button wire:click="edit({{ $pengadaan->id }})"
                                            wire:loading.attr="disabled" wire:target="edit({{ $pengadaan->id }})"
                                            wire:key="btn-edit-{{ $pengadaan->id }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 disabled:opacity-50"
                                            title="Edit">
                                            <svg wire:loading.class="hidden" wire:target="edit({{ $pengadaan->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <svg wire:loading wire:target="edit({{ $pengadaan->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endcan
                                    @can('delete', $pengadaan)
                                        @if($pengadaan->invoices_count === 0)
                                        <button wire:click="confirmDelete({{ $pengadaan->id }})"
                                            wire:loading.attr="disabled" wire:target="confirmDelete({{ $pengadaan->id }})"
                                            wire:key="btn-delete-{{ $pengadaan->id }}"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                                            title="Hapus">
                                            <svg wire:loading.class="hidden" wire:target="confirmDelete({{ $pengadaan->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <svg wire:loading wire:target="confirmDelete({{ $pengadaan->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada pengadaan ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $pengadaans->links() }}
        </div>
    </div>

    <x-delete-modal
        :show="$showDeleteModal"
        wire:model="showDeleteModal"
        title="Hapus Pengadaan"
        message="Apakah Anda yakin ingin menghapus pengadaan"
        :itemName="$deletingPengadaanName ?? ''"
        confirmMethod="delete"
    />

    @if($showApproveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showApproveModal', false)"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="mt-3 text-left sm:mt-0 sm:ml-4 flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Setujui Pengadaan</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Apakah Anda yakin ingin menyetujui pengadaan <strong>{{ $reviewingPengadaanName }}</strong>?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="approve"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-not-allowed"
                            wire:target="approve">
                            <svg wire:loading wire:target="approve" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
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

    @if($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showRejectModal', false)"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="reject">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tolak Pengadaan</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pengadaan: {{ $reviewingPengadaanName }}</p>
                            <x-input-label for="rejectionReason" value="Alasan Penolakan" :required="true" />
                            <textarea wire:model="rejectionReason" id="rejectionReason" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Jelaskan alasan penolakan..."></textarea>
                            <x-input-error :messages="$errors->get('rejectionReason')" class="mt-2" />
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed"
                                wire:target="reject">
                                <svg wire:loading wire:target="reject" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Tolak
                            </button>
                            <x-cancel-button wire:click="$set('showRejectModal', false)" target="closeRejectModal" wire:key="btn-reject-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
