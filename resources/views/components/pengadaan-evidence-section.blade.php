@props(['evidences'])

<div class="mt-6">
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Evidence / Dokumen Pengadaan
            <span class="ml-2 inline-flex items-center whitespace-nowrap px-2 py-0.5 text-xs font-semibold rounded-full @if(count($evidences) > 0) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                {{ count($evidences) }} evidence
            </span>
        </label>
        <button type="button" wire:click="addEvidence" wire:key="add-evidence-btn"
            wire:loading.attr="disabled" wire:target="addEvidence"
            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors disabled:opacity-50">
            <svg wire:loading.remove wire:target="addEvidence" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg wire:loading wire:target="addEvidence" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove wire:target="addEvidence">Tambah Evidence</span>
            <span wire:loading wire:target="addEvidence">Memproses...</span>
        </button>
    </div>

    @if(count($evidences) > 0)
        <div class="space-y-3">
            @foreach($evidences as $index => $evidence)
                @php
                    $isExisting = isset($evidence['id']) && is_numeric($evidence['id']);
                @endphp
                <div wire:key="pengadaan-evidence-{{ $evidence['id'] ?? $index }}" class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    @if($isExisting)
                        {{-- Existing Data - Read Only Display --}}
                        @php
                            $isProcessing = isset($evidence['file_status']) && in_array($evidence['file_status'], ['pending', 'processing']);
                            $isFailed = isset($evidence['file_status']) && $evidence['file_status'] === 'failed';
                            $isCompleted = isset($evidence['file_status']) && $evidence['file_status'] === 'completed';
                        @endphp
                        <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            @if($isProcessing)
                                <svg class="animate-spin w-5 h-5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @elseif($isFailed)
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $evidence['name'] ?? 'Evidence' }}</p>
                                @if(isset($evidence['file_name']) && $evidence['file_name'])
                                    @php
                                        $extension = strtoupper(pathinfo($evidence['file_name'], PATHINFO_EXTENSION));
                                    @endphp
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $evidence['file_name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $extension }}
                                        @if(isset($evidence['file_size']))
                                            • {{ number_format($evidence['file_size'] / 1024, 1) }} KB
                                        @endif
                                    </p>
                                @endif
                                @if($isProcessing)
                                    <p class="text-xs text-blue-600 dark:text-blue-400">Sedang diproses...</p>
                                @elseif($isFailed && isset($evidence['file_error']))
                                    <p class="text-xs text-red-600 dark:text-red-400">{{ $evidence['file_error'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                @if(isset($evidence['file_name']) && $evidence['file_name'] && $isCompleted)
                                    <button type="button" wire:click="downloadEvidenceFile({{ $index }})"
                                        wire:loading.attr="disabled" wire:target="downloadEvidenceFile({{ $index }})"
                                        class="text-blue-500 hover:text-blue-700 p-1.5 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50"
                                        title="Download">
                                        <svg wire:loading.remove wire:target="downloadEvidenceFile({{ $index }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <svg wire:loading wire:target="downloadEvidenceFile({{ $index }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                @endif
                                <button type="button"
                                    wire:click="removeEvidence({{ $index }})"
                                    wire:loading.attr="disabled"
                                    wire:target="removeEvidence({{ $index }})"
                                    class="text-red-400 hover:text-red-600 p-1 disabled:opacity-50" title="Hapus evidence">
                                    <svg wire:loading.class="hidden" wire:target="removeEvidence({{ $index }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <svg wire:loading wire:target="removeEvidence({{ $index }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- New Data - Input Fields --}}
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex-1 min-w-0">
                                <input type="text" wire:model="evidences.{{ $index }}.name"
                                    placeholder="Nama evidence (wajib)"
                                    class="w-full px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:focus:ring-blue-600 transition-colors">
                                @error('evidences.'.$index.'.name')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="button"
                                wire:click="removeEvidence({{ $index }})"
                                wire:loading.attr="disabled"
                                wire:target="removeEvidence({{ $index }})"
                                class="text-red-400 hover:text-red-600 p-1 disabled:opacity-50" title="Hapus evidence">
                                <svg wire:loading.class="hidden" wire:target="removeEvidence({{ $index }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <svg wire:loading wire:target="removeEvidence({{ $index }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- File Upload (drag-and-drop + click) --}}
                        <div>
                            @if(isset($evidence['file']) && $evidence['file'] && is_object($evidence['file']))
                            {{-- File Selected (New Upload) --}}
                            <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs text-blue-700 dark:text-blue-300 truncate flex-1">{{ $evidence['file']->getClientOriginalName() }}</span>
                                <span class="text-xs text-blue-500 dark:text-blue-400">{{ number_format($evidence['file']->getSize() / 1024, 0) }} KB</span>
                                <button type="button" wire:click="removeEvidenceFile({{ $index }})"
                                    wire:loading.attr="disabled"
                                    wire:target="removeEvidenceFile({{ $index }})"
                                    class="text-red-500 hover:text-red-700 p-0.5 disabled:opacity-50" title="Hapus file">
                                    <svg wire:loading.class="hidden" wire:target="removeEvidenceFile({{ $index }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <svg wire:loading wire:target="removeEvidenceFile({{ $index }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        @else
                            {{-- Drag-and-drop Upload Area --}}
                            <x-file-dropzone
                                wire-model="evidences.{{ $index }}.file"
                                id="pengadaan-evidence-file-{{ $index }}"
                                :compact="true"
                                :helper-text="get_upload_config_display('pengadaan-evidence')"
                                :accept="'.'.implode(',.', get_allowed_mimes_array('pengadaan-evidence'))"
                            />
                        @endif
                        @error('evidences.'.$index.'.file')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6 text-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Belum ada evidence
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Klik "Tambah Evidence" untuk menambahkan dokumen PO/kontrak pengadaan
            </p>
        </div>
    @endif
</div>
