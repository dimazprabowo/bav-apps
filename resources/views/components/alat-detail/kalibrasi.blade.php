@props(['alat'])

@php
    $hasProcessing = $alat->kalibrasis->contains(fn ($k) => $k->isProcessing());
@endphp

<div class="px-4 py-5 sm:p-6 border-t border-gray-200 dark:border-gray-700" @if($hasProcessing) wire:poll.10s @endif>
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
            History Kalibrasi
            <span class="ml-2 inline-flex items-center whitespace-nowrap px-2 py-0.5 text-xs font-semibold rounded-full @if($alat->kalibrasis->isNotEmpty()) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                {{ $alat->kalibrasis->count() }} record
            </span>
        </h4>
        @can('update', $alat)
            <x-loading-button wire:click="openCreateKalibrasiModal" target="openCreateKalibrasiModal" wire:key="btn-add-kalibrasi" type="button" variant="secondary" size="sm" loadingText="...">
                <x-slot:icon><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
                Tambah Kalibrasi
            </x-loading-button>
        @endcan
    </div>

    @if($alat->kalibrasis->isEmpty())
        <div class="p-6 text-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada history kalibrasi</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Klik "Tambah Kalibrasi" untuk menambah record kalibrasi</p>
        </div>
    @else
        {{-- Timeline view --}}
        <div class="space-y-3">
            @foreach($alat->kalibrasis as $kalibrasi)
                <div wire:key="kalibrasi-{{ $kalibrasi->id }}" class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $kalibrasi->tanggal_kalibrasi->format('d/m/Y') }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $kalibrasi->hasil->badgeClass() }}">
                                    {{ $kalibrasi->hasil->label() }}
                                </span>
                                @if($kalibrasi->tanggal_kalibrasi_berikutnya)
                                    @if($kalibrasi->isExpired())
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            Expired {{ $kalibrasi->tanggal_kalibrasi_berikutnya->format('d/m/Y') }}
                                        </span>
                                    @elseif($kalibrasi->isExpiringSoon())
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                            Jatuh tempo {{ $kalibrasi->tanggal_kalibrasi_berikutnya->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                            Berikutnya {{ $kalibrasi->tanggal_kalibrasi_berikutnya->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                            <dl class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Vendor</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $kalibrasi->vendor ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">No. Sertifikat</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $kalibrasi->sertifikat_no ?: '-' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500 dark:text-gray-400">Catatan</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $kalibrasi->catatan ?: '-' }}</dd>
                                </div>
                            </dl>
                            {{-- File status --}}
                            @if($kalibrasi->hasFile() || $kalibrasi->isProcessing())
                                <div class="mt-3 flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    @if($kalibrasi->isProcessing())
                                        <svg class="animate-spin w-4 h-4 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-xs text-blue-600 dark:text-blue-400">Sertifikat sedang diproses...</span>
                                    @elseif($kalibrasi->isFailed())
                                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-xs text-red-600 dark:text-red-400">Gagal: {{ $kalibrasi->file_error }}</span>
                                    @elseif($kalibrasi->isCompleted())
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-xs text-gray-700 dark:text-gray-300 truncate flex-1">{{ $kalibrasi->file_name }}</span>
                                        <button type="button" wire:click="downloadKalibrasiFile({{ $kalibrasi->id }})"
                                            wire:loading.attr="disabled" wire:target="downloadKalibrasiFile({{ $kalibrasi->id }})"
                                            wire:key="btn-dl-kal-{{ $kalibrasi->id }}"
                                            class="text-blue-500 hover:text-blue-700 p-1 disabled:opacity-50" title="Download sertifikat">
                                            <svg wire:loading.remove wire:target="downloadKalibrasiFile({{ $kalibrasi->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <svg wire:loading wire:target="downloadKalibrasiFile({{ $kalibrasi->id }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @can('update', $alat)
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" wire:click="openEditKalibrasiModal({{ $kalibrasi->id }})"
                                    wire:loading.attr="disabled" wire:target="openEditKalibrasiModal({{ $kalibrasi->id }})"
                                    wire:key="btn-edit-kal-{{ $kalibrasi->id }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 p-1 disabled:opacity-50" title="Edit kalibrasi">
                                    <svg wire:loading.class="hidden" wire:target="openEditKalibrasiModal({{ $kalibrasi->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <svg wire:loading wire:target="openEditKalibrasiModal({{ $kalibrasi->id }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                                <button type="button" wire:click="confirmDeleteKalibrasi({{ $kalibrasi->id }})"
                                    wire:loading.attr="disabled" wire:target="confirmDeleteKalibrasi({{ $kalibrasi->id }})"
                                    wire:key="btn-del-kal-{{ $kalibrasi->id }}"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1 disabled:opacity-50" title="Hapus kalibrasi">
                                    <svg wire:loading.class="hidden" wire:target="confirmDeleteKalibrasi({{ $kalibrasi->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <svg wire:loading wire:target="confirmDeleteKalibrasi({{ $kalibrasi->id }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
