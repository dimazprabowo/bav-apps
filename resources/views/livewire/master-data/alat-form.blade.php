<div wire:key="alat-form-{{ $editMode ? 'edit-'.$alatId : 'create' }}" class="w-full">
    <form wire:submit="save" enctype="multipart/form-data">
        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="p-6">
                {{-- Section: Informasi Dasar --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="code" value="Kode Alat" :required="true" />
                        <x-text-input wire:model="code" id="code" type="text" class="mt-1 block w-full" placeholder="Mis. ALT-001" />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nama Alat" :required="true" />
                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="Mis. Multimeter Digital" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="merk_type" value="Merk/Type" />
                        <x-text-input wire:model="merk_type" id="merk_type" type="text" class="mt-1 block w-full" placeholder="Mis. Fluke 87V" />
                        <x-input-error :messages="$errors->get('merk_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="serial_number" value="Serial Number" />
                        <x-text-input wire:model="serial_number" id="serial_number" type="text" class="mt-1 block w-full" placeholder="Mis. SN-12345678" />
                        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="kode_inventaris" value="Kode Inventaris" />
                        <x-text-input wire:model="kode_inventaris" id="kode_inventaris" type="text" class="mt-1 block w-full" placeholder="Mis. INV-ALT-001" />
                        <x-input-error :messages="$errors->get('kode_inventaris')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Deskripsi alat..."></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="cabang_id" value="Cabang" :required="true" />
                        <x-searchable-select wire:model.live="cabang_id" :options="$this->cabangOptions" placeholder="Pilih cabang" searchPlaceholder="Cari cabang..." />
                        <x-input-error :messages="$errors->get('cabang_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="lokasi" value="Lokasi" />
                        <x-text-input wire:model="lokasi" id="lokasi" type="text" class="mt-1 block w-full" placeholder="Mis. Ruang Lab 1" />
                        <x-input-error :messages="$errors->get('lokasi')" class="mt-2" />
                    </div>
                </div>

                {{-- Section: Kondisi & Status --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Kondisi & Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="kondisi" value="Kondisi" :required="true" />
                            <x-searchable-select wire:model.live="kondisi" :options="$this->kondisiOptions" placeholder="Pilih kondisi" />
                            <x-input-error :messages="$errors->get('kondisi')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="status_kepemilikan" value="Status Kepemilikan" :required="true" />
                            <x-searchable-select wire:model.live="status_kepemilikan" :options="$this->kepemilikanOptions" placeholder="Pilih kepemilikan" />
                            <x-input-error :messages="$errors->get('status_kepemilikan')" class="mt-2" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-900 dark:text-white">Alat aktif dan dapat digunakan</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Catatan: History kalibrasi dikelola terpisah di halaman detail alat.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section: Evidence / Dokumen --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <x-alat-evidence-section :evidences="$evidences" />
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
