<div wire:key="pengadaan-form-{{ $editMode ? 'edit-'.$pengadaanId : 'create' }}" class="w-full">
    <form wire:submit="save" enctype="multipart/form-data">
        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="p-6">
                {{-- Section: Informasi Pengadaan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="no_pengadaan" value="No. Pengadaan" :required="true" />
                        <x-text-input wire:model="no_pengadaan" id="no_pengadaan" type="text" class="mt-1 block w-full" placeholder="Mis. PG-2026-001" />
                        <x-input-error :messages="$errors->get('no_pengadaan')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tanggal_pengadaan" value="Tanggal Pengadaan" :required="true" />
                        <x-text-input wire:model="tanggal_pengadaan" id="tanggal_pengadaan" type="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('tanggal_pengadaan')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="klaster_id" value="Klaster Vendor" :required="true" />
                        <x-searchable-select wire:model.live="klaster_id" :options="$this->klasterOptions" placeholder="Pilih klaster" searchPlaceholder="Cari klaster..." />
                        <x-input-error :messages="$errors->get('klaster_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="vendor_id" value="Vendor" :required="true" />
                        <x-searchable-select wire:model="vendor_id" :options="$this->vendorOptions" placeholder="{{ $klaster_id ? 'Pilih vendor' : 'Pilih klaster terlebih dahulu' }}" searchPlaceholder="Cari vendor..." wire:key="vendor-select-{{ $klaster_id ?? 'none' }}" />
                        <x-input-error :messages="$errors->get('vendor_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="cabang_id" value="Cabang" />
                        <x-searchable-select wire:model="cabang_id" :options="$this->cabangOptions" placeholder="Pengadaan pusat (tidak terikat cabang)" searchPlaceholder="Cari cabang..." />
                        <x-input-error :messages="$errors->get('cabang_id')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="catatan" value="Catatan" />
                        <textarea wire:model="catatan" id="catatan" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Catatan pengadaan..."></textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>
                </div>

                {{-- Section: Item Aset --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <x-pengadaan-item-section :items="$items" :totalBiaya="$this->totalBiaya" :satuanOptions="$this->satuanOptions" />
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </div>

                {{-- Section: Evidence / Dokumen --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <x-pengadaan-evidence-section :evidences="$evidences" />
                </div>
            </div>
        </div>

        {{-- Action Bar (separate card, bos-apps pattern) --}}
        <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-4">
            <x-cancel-button wire:click="cancel" target="cancel" wire:key="btn-cancel" variant="secondary" size="lg" class="w-full sm:w-auto" />
            <x-loading-button type="submit" target="save" wire:key="btn-save" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                {{ $editMode ? 'Update' : 'Simpan' }}
            </x-loading-button>
        </div>
    </form>
</div>
