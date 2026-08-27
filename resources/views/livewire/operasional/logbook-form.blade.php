<div wire:key="logbook-form-{{ $editMode ? 'edit-'.$logId : 'create' }}" class="w-full">
    <form wire:submit="save">
        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="cabang_id" value="Cabang" :required="true" />
                        <x-searchable-select wire:model.live="cabang_id" :options="$this->cabangOptions" placeholder="Pilih cabang" searchPlaceholder="Cari cabang..." />
                        <x-input-error :messages="$errors->get('cabang_id')" class="mt-2" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih cabang untuk melihat alat tersedia</p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <x-input-label for="alat_id" value="Alat" :required="true" />
                            <svg wire:loading wire:target="cabang_id" class="animate-spin w-3.5 h-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <x-searchable-select wire:model.live="alat_id" :options="$this->alatOptions" :placeholder="$cabang_id ? 'Pilih alat' : 'Pilih cabang dulu'" searchPlaceholder="Cari alat..." wire:key="alat-select-{{ $cabang_id }}" wire:loading.attr="disabled" wire:target="cabang_id" />
                        <x-input-error :messages="$errors->get('alat_id')" class="mt-2" />
                        @if($cabang_id)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alat yang sedang dipinjam/diajukan ditandai dan tidak dapat dipilih.</p>
                        @endif
                    </div>
                    <div>
                        <x-input-label for="peminjam_id" value="Peminjam" :required="true" />
                        <x-searchable-select wire:model.live="peminjam_id" :options="$this->peminjamOptions" placeholder="Pilih peminjam" searchPlaceholder="Cari peminjam..." />
                        <x-input-error :messages="$errors->get('peminjam_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="kondisi_pinjam" value="Kondisi Saat Dipinjam" :required="true" />
                        <x-searchable-select wire:model.live="kondisi_pinjam" :options="$this->kondisiOptions" placeholder="Pilih kondisi" />
                        <x-input-error :messages="$errors->get('kondisi_pinjam')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tanggal_pinjam" value="Tanggal Pinjam" :required="true" />
                        <x-text-input wire:model="tanggal_pinjam" id="tanggal_pinjam" type="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('tanggal_pinjam')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tanggal_kembali_rencana" value="Rencana Tanggal Kembali" :required="true" />
                        <x-text-input wire:model="tanggal_kembali_rencana" id="tanggal_kembali_rencana" type="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('tanggal_kembali_rencana')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="catatan" value="Catatan (opsional)" />
                        <textarea wire:model="catatan" id="catatan" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Catatan peminjaman..."></textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>
                </div>

                @if($editMode)
                    <div class="mt-6 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-400">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Mengubah logbook yang sudah diproses tidak disarankan.
                        </p>
                    </div>
                @else
                    <div class="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-400">
                            Setelah diajukan, peminjaman akan menunggu approval dari atasan/penjaga alat.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Action Bar (separate card, bos-apps pattern) --}}
        <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-4">
            <x-cancel-button wire:click="cancel" target="cancel" wire:key="btn-cancel" variant="secondary" size="lg" class="w-full sm:w-auto" />
            <x-loading-button type="submit" target="save" wire:key="btn-save" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                {{ $editMode ? 'Update' : 'Ajukan Peminjaman' }}
            </x-loading-button>
        </div>
    </form>
</div>
