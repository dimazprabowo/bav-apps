<div wire:key="alat-detail-{{ $alat->id }}" class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        {{-- Header --}}
        <div class="px-4 py-5 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $alat->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $alat->code }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alat->kondisi->badgeClass() }}">{{ $alat->kondisi->label() }}</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alat->status_kalibrasi_derived->badgeClass() }}">{{ $alat->status_kalibrasi_derived->label() }}</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alat->review_status->badgeClass() }}">{{ $alat->review_status->label() }}</span>
                </div>
            </div>
        </div>

        {{-- Sections --}}
        <x-alat-detail.information :alat="$alat" />
        <x-alat-detail.review :alat="$alat" />
        <x-alat-detail.kalibrasi :alat="$alat" />
        <x-alat-detail.peminjaman :alat="$alat" />
        <x-alat-detail.evidence :alat="$alat" />
    </div>

    {{-- Action Bar (separate card, bos-apps pattern) --}}
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-4">
        <x-cancel-button wire:click="goBack" target="goBack" wire:key="btn-back" label="Kembali" variant="secondary" size="lg" class="w-full sm:w-auto" />
    </div>

    {{-- Modal: Tambah/Edit Kalibrasi (pola bos-apps: @if, instant unload) --}}
    @if($showKalibrasiModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showKalibrasiModal', false)"></div>

                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="saveKalibrasi" enctype="multipart/form-data">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editingKalibrasiId ? 'Edit Kalibrasi' : 'Tambah Kalibrasi' }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="kal_tanggal" value="Tanggal Kalibrasi" :required="true" />
                                    <x-text-input wire:model="kal_tanggal" id="kal_tanggal" type="date" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('kal_tanggal')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="kal_tanggal_berikutnya" value="Kalibrasi Berikutnya" />
                                    <x-text-input wire:model="kal_tanggal_berikutnya" id="kal_tanggal_berikutnya" type="date" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('kal_tanggal_berikutnya')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="kal_vendor" value="Vendor Kalibrasi" />
                                    <x-text-input wire:model="kal_vendor" id="kal_vendor" type="text" class="mt-1 block w-full" placeholder="Mis. Kalibrasi Teknik Nusantara" />
                                    <x-input-error :messages="$errors->get('kal_vendor')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="kal_sertifikat_no" value="No. Sertifikat" />
                                    <x-text-input wire:model="kal_sertifikat_no" id="kal_sertifikat_no" type="text" class="mt-1 block w-full" placeholder="Mis. KAL-2026-001" />
                                    <x-input-error :messages="$errors->get('kal_sertifikat_no')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="kal_hasil" value="Hasil Kalibrasi" :required="true" />
                                    <x-searchable-select wire:model.live="kal_hasil" :options="$this->hasilOptions" placeholder="Pilih hasil" />
                                    <x-input-error :messages="$errors->get('kal_hasil')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="kal_catatan" value="Catatan" />
                                    <textarea wire:model="kal_catatan" id="kal_catatan" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Catatan kalibrasi..."></textarea>
                                    <x-input-error :messages="$errors->get('kal_catatan')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="kal_file" value="Sertifikat File (PDF/JPG/PNG, max 20MB)" />

                                    @if($kal_file && is_object($kal_file))
                                        {{-- File Selected State (match evidence section pattern) --}}
                                        <div class="flex items-center gap-2 px-3 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-xs text-blue-700 dark:text-blue-300 truncate flex-1">{{ $kal_file->getClientOriginalName() }}</span>
                                            <span class="text-xs text-blue-500 dark:text-blue-400">{{ number_format($kal_file->getSize() / 1024, 0) }} KB</span>
                                            <button type="button" wire:click="removeKalibrasiFile"
                                                wire:loading.attr="disabled"
                                                wire:target="removeKalibrasiFile"
                                                wire:key="btn-remove-kal-file"
                                                class="text-red-500 hover:text-red-700 p-0.5 disabled:opacity-50" title="Hapus file">
                                                <svg wire:loading.class="hidden" wire:target="removeKalibrasiFile" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <svg wire:loading wire:target="removeKalibrasiFile" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Upload Area with Loading Progress --}}
                                        <div x-data="{ uploading: false, progress: 0 }"
                                             x-on:livewire-upload-start="uploading = true"
                                             x-on:livewire-upload-finish="uploading = false; progress = 0"
                                             x-on:livewire-upload-cancel="uploading = false"
                                             x-on:livewire-upload-error="uploading = false"
                                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                                            <label class="flex flex-col items-center justify-center w-full px-3 py-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-white dark:bg-gray-800">
                                                <div x-show="!uploading" class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    <span>Klik untuk upload sertifikat kalibrasi</span>
                                                </div>
                                                <div x-show="uploading" x-cloak class="flex items-center justify-center gap-2">
                                                    <svg class="animate-spin w-3 h-3 text-blue-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span class="text-xs text-blue-600" x-text="progress + '%'"></span>
                                                </div>
                                                <input type="file" wire:model="kal_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                            </label>
                                        </div>
                                    @endif

                                    <x-input-error :messages="$errors->get('kal_file')" class="mt-2" />
                                    @if($editingKalibrasiId)
                                        @php
                                            $editingKalibrasi = $alat->kalibrasis->firstWhere('id', $editingKalibrasiId);
                                        @endphp
                                        @if($editingKalibrasi && $editingKalibrasi->file_name)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                File saat ini: {{ $editingKalibrasi->file_name }}
                                                (upload file baru untuk ganti)
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="saveKalibrasi" wire:key="btn-save-kal" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                                {{ $editingKalibrasiId ? 'Update' : 'Simpan' }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeKalibrasiModal" target="closeKalibrasiModal" wire:key="btn-cancel-kal" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Delete Kalibrasi Confirmation (pola bos-apps: x-delete-modal dengan confirmMethod) --}}
    <x-delete-modal
        :show="$showDeleteKalibrasiModal"
        wire:model="showDeleteKalibrasiModal"
        wire:key="modal-delete-kalibrasi"
        title="Hapus Data Kalibrasi"
        message="Apakah Anda yakin ingin menghapus data kalibrasi ini? File sertifikat terkait juga akan dihapus. Aksi ini tidak dapat dibatalkan."
        confirmMethod="deleteKalibrasi"
    />
</div>
