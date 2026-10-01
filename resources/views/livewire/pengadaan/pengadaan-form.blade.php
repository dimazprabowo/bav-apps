<div wire:key="pengadaan-form-{{ $editMode ? 'edit-'.$pengadaanId : 'create' }}" class="w-full">
    <form wire:submit="save" enctype="multipart/form-data">
        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="p-6">
                {{-- Section: Informasi Pemohon & Biaya --}}
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informasi Pemohon &amp; Biaya</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nama_pemohon" value="Nama Pemohon" :required="true" />
                        <x-text-input wire:model="nama_pemohon" id="nama_pemohon" type="text" class="mt-1 block w-full" placeholder="Mis. Budi Santoso" />
                        <x-input-error :messages="$errors->get('nama_pemohon')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tipe_biaya" value="Tipe Biaya" :required="true" />
                        <x-searchable-select wire:model.live="tipe_biaya" :options="$this->tipeBiayaOptions" placeholder="Pilih tipe biaya" />
                        <x-input-error :messages="$errors->get('tipe_biaya')" class="mt-2" />
                    </div>
                    @if($tipe_biaya === \App\Enums\TipeBiaya::RabProject->value)
                        <div>
                            <x-input-label for="nama_project" value="Nama Project" :required="true" />
                            <x-text-input wire:model="nama_project" id="nama_project" type="text" class="mt-1 block w-full" placeholder="Mis. Project Kalibrasi Tahap 1" />
                            <x-input-error :messages="$errors->get('nama_project')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="no_wbs" value="No. WBS" :required="true" />
                            <x-text-input wire:model="no_wbs" id="no_wbs" type="text" class="mt-1 block w-full" placeholder="Mis. WBS-2025-001" />
                            <x-input-error :messages="$errors->get('no_wbs')" class="mt-2" />
                        </div>
                    @endif
                </div>

                {{-- Section: Informasi Pengadaan --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
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
                        <x-searchable-select wire:model.live="vendor_id" :options="$this->vendorOptions" placeholder="{{ $klaster_id ? 'Pilih vendor' : 'Pilih klaster terlebih dahulu' }}" searchPlaceholder="Cari vendor..." wire:key="vendor-select-{{ $klaster_id ?? 'none' }}" />
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
                </div>

                {{-- Section: Item Aset --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <x-pengadaan-item-section :items="$items" :totalBiaya="$this->totalBiaya" :satuanOptions="$this->satuanOptions" :kategoriItemOptions="$this->kategoriItemOptions" :vendorId="$vendor_id" />
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
