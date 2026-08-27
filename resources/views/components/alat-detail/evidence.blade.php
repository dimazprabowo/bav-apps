@props(['alat'])

@php
    $hasProcessing = $alat->evidences->contains(fn ($e) => $e->isProcessing());
@endphp

<div class="px-4 py-5 sm:p-6 border-t border-gray-200 dark:border-gray-700" @if($hasProcessing) wire:poll.10s @endif>
    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Evidence / Dokumen</h4>
    @if($alat->evidences->isEmpty())
        <div class="p-6 text-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada evidence</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Evidence dikelola di halaman edit alat</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($alat->evidences as $evidence)
                <div wire:key="evidence-{{ $evidence->id }}" class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        @if($evidence->isProcessing())
                            <svg class="animate-spin w-5 h-5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        @elseif($evidence->isFailed())
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($evidence->isCompleted())
                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $evidence->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if($evidence->isProcessing())
                                    <span class="text-blue-600 dark:text-blue-400">Sedang diproses...</span>
                                @elseif($evidence->isFailed())
                                    <span class="text-red-600 dark:text-red-400">Gagal: {{ $evidence->file_error ?: 'Terjadi kesalahan' }}</span>
                                @elseif($evidence->isCompleted())
                                    {{ $evidence->file_name }} ({{ number_format($evidence->file_size / 1024, 1) }} KB)
                                @else
                                    Menunggu proses...
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($evidence->isCompleted())
                        <x-loading-button wire:click="downloadEvidence({{ $evidence->id }})" target="downloadEvidence({{ $evidence->id }})" wire:key="btn-download-{{ $evidence->id }}" variant="secondary" size="sm" loadingText="...">
                            Download
                        </x-loading-button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
