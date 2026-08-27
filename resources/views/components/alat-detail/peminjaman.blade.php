@props(['alat'])

<div class="px-4 py-5 sm:p-6 border-t border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
            History Peminjaman
            <span class="ml-2 inline-flex items-center whitespace-nowrap px-2 py-0.5 text-xs font-semibold rounded-full @if($alat->logBookPeminjaman->isNotEmpty()) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                {{ $alat->logBookPeminjaman->count() }} record
            </span>
        </h4>
    </div>

    @if($alat->logBookPeminjaman->isEmpty())
        <div class="p-6 text-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada history peminjaman</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Alat ini belum pernah dipinjam</p>
        </div>
    @else
        {{-- Timeline view --}}
        <div class="space-y-3">
            @foreach($alat->logBookPeminjaman as $log)
                <div wire:key="log-{{ $log->id }}" class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $log->tanggal_pinjam->format('d/m/Y') }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $log->status->badgeClass() }}">
                                    {{ $log->status->label() }}
                                </span>
                                @if($log->is_overdue)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                        Terlambat
                                    </span>
                                @endif
                            </div>
                            <dl class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Peminjam</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $log->peminjam?->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Cabang Peminjaman</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $log->cabang?->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Tgl Kembali Rencana</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $log->tanggal_kembali_rencana?->format('d/m/Y') ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Tgl Kembali Aktual</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $log->tanggal_kembali_aktual?->format('d/m/Y') ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Kondisi Pinjam</dt>
                                    <dd class="mt-0.5"><span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $log->kondisi_pinjam->badgeClass() }}">{{ $log->kondisi_pinjam->label() }}</span></dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Kondisi Kembali</dt>
                                    <dd class="mt-0.5">
                                        @if($log->kondisi_kembali)
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $log->kondisi_kembali->badgeClass() }}">{{ $log->kondisi_kembali->label() }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </dd>
                                </div>
                                @if($log->approver)
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Disetujui Oleh</dt>
                                        <dd class="text-gray-900 dark:text-white">{{ $log->approver->name }}</dd>
                                    </div>
                                @endif
                                @if($log->catatan)
                                    <div class="sm:col-span-2">
                                        <dt class="text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="text-gray-900 dark:text-white">{{ $log->catatan }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
