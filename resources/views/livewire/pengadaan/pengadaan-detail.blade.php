<div class="w-full space-y-6">
    {{-- Header Info Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $pengadaan->no_pengadaan }}</h2>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $pengadaan->status_approval->badgeClass() }}">
                        {{ $pengadaan->status_approval->label() }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $pengadaan->tanggal_pengadaan->format('d F Y') }} • Vendor: {{ $pengadaan->vendor->name }} ({{ $pengadaan->vendor->klaster->name }})
                    • Cabang: {{ $pengadaan->cabang->name ?? 'Pusat' }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Biaya</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($pengadaan->total_biaya, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($pengadaan->catatan)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $pengadaan->catatan }}</p>
            </div>
        @endif

        @if($pengadaan->isApproved() && $pengadaan->approver)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-green-700 dark:text-green-400">
                    Disetujui oleh {{ $pengadaan->approver->name }} pada {{ $pengadaan->approved_at->format('d/m/Y H:i') }}
                </p>
            </div>
        @endif

        @if($pengadaan->isRejected())
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-red-700 dark:text-red-400">
                    Ditolak oleh {{ $pengadaan->approver->name ?? '-' }} pada {{ $pengadaan->approved_at?->format('d/m/Y H:i') }}
                </p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">Alasan: {{ $pengadaan->rejection_reason }}</p>
            </div>
        @endif
    </div>

    {{-- Item --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item ({{ $pengadaan->items->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Item</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pengadaan->items as $item)
                        <tr wire:key="item-{{ $item->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->nama_item }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->kategori_item ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">{{ $item->qty }} {{ $item->satuan?->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada item</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Evidence --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Evidence / Dokumen ({{ $pengadaan->evidences->count() }})</h3>
        @if($pengadaan->evidences->count() > 0)
            <div class="space-y-3">
                @foreach($pengadaan->evidences as $evidence)
                    <div wire:key="evidence-{{ $evidence->id }}" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        @if($evidence->isProcessing())
                            <svg class="animate-spin w-5 h-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        @elseif($evidence->file_status === 'failed')
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $evidence->name }}</p>
                            @if($evidence->file_name)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $evidence->file_name }} • {{ number_format(($evidence->file_size ?? 0) / 1024, 1) }} KB</p>
                            @endif
                        </div>
                        @if($evidence->isCompleted())
                            <button wire:click="downloadEvidence({{ $evidence->id }})"
                                wire:loading.attr="disabled" wire:target="downloadEvidence({{ $evidence->id }})"
                                class="text-blue-500 hover:text-blue-700 p-1.5 disabled:opacity-50" title="Download">
                                <svg wire:loading.class="hidden" wire:target="downloadEvidence({{ $evidence->id }})" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <svg wire:loading wire:target="downloadEvidence({{ $evidence->id }})" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada evidence.</p>
        @endif
    </div>

    {{-- Invoice & Pembayaran --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Invoice & Pembayaran</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Status Invoice:
                    <span class="px-1.5 py-0.5 rounded {{ $pengadaan->status_invoice->badgeClass() }}">{{ $pengadaan->status_invoice->label() }}</span>
                    • Status Pembayaran:
                    <span class="px-1.5 py-0.5 rounded {{ $pengadaan->status_pembayaran->badgeClass() }}">{{ $pengadaan->status_pembayaran->label() }}</span>
                </p>
            </div>
            @can('update', $pengadaan)
                <x-loading-button wire:click="openCreateInvoiceModal" target="openCreateInvoiceModal" wire:key="btn-add-invoice" variant="primary" size="md" loadingText="Memuat...">
                    Tambah Invoice
                </x-loading-button>
            @endcan
        </div>

        @if($pengadaan->invoices->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700"
                @if($pengadaan->invoices->contains(fn($inv) => $inv->isProcessing())) wire:poll.15s="loadPengadaan" @endif>
                @foreach($pengadaan->invoices as $invoice)
                    <div wire:key="invoice-{{ $invoice->id }}" class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->no_invoice }}</p>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $invoice->status_pembayaran->badgeClass() }}">
                                        {{ $invoice->status_pembayaran->label() }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $invoice->tanggal_invoice->format('d/m/Y') }}
                                    @if($invoice->jatuh_tempo)
                                        • Jatuh tempo: {{ $invoice->jatuh_tempo->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah Tagihan</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($invoice->jumlah, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($invoice->isProcessing())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $invoice->file_status_enum->badgeClass() }}" title="File sedang diproses oleh sistem">
                                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            {{ $invoice->file_status_enum->label() }}
                                        </span>
                                    @elseif($invoice->isFailed())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $invoice->file_status_enum->badgeClass() }}" title="{{ $invoice->file_error ?? 'Gagal memproses file' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $invoice->file_status_enum->label() }}
                                        </span>
                                    @elseif($invoice->isCompleted())
                                        <button wire:click="downloadInvoiceFile({{ $invoice->id }})"
                                            wire:loading.attr="disabled" wire:target="downloadInvoiceFile({{ $invoice->id }})"
                                            class="text-blue-500 hover:text-blue-700 p-1.5 disabled:opacity-50" title="Download invoice">
                                            <svg wire:loading.class="hidden" wire:target="downloadInvoiceFile({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <svg wire:loading wire:target="downloadInvoiceFile({{ $invoice->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endif
                                    @can('update', $pengadaan)
                                        <button wire:click="openEditInvoiceModal({{ $invoice->id }})"
                                            wire:loading.attr="disabled" wire:target="openEditInvoiceModal({{ $invoice->id }})"
                                            class="text-gray-500 hover:text-gray-700 p-1.5 disabled:opacity-50" title="Edit invoice">
                                            <svg wire:loading.class="hidden" wire:target="openEditInvoiceModal({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <svg wire:loading wire:target="openEditInvoiceModal({{ $invoice->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                        <button wire:click="confirmDeleteInvoice({{ $invoice->id }})"
                                            wire:loading.attr="disabled" wire:target="confirmDeleteInvoice({{ $invoice->id }})"
                                            class="text-red-500 hover:text-red-700 p-1.5 disabled:opacity-50" title="Hapus invoice">
                                            <svg wire:loading.class="hidden" wire:target="confirmDeleteInvoice({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <svg wire:loading wire:target="confirmDeleteInvoice({{ $invoice->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        {{-- Payments per invoice --}}
                        <div class="mt-4 pl-4 border-l-2 border-gray-100 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Riwayat Pembayaran ({{ $invoice->payments->count() }})</p>
                                @can('update', $pengadaan)
                                    <button wire:click="openCreatePaymentModal({{ $invoice->id }})"
                                        wire:loading.attr="disabled" wire:target="openCreatePaymentModal({{ $invoice->id }})"
                                        class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 disabled:opacity-50">
                                        + Tambah Pembayaran
                                    </button>
                                @endcan
                            </div>

                            @forelse($invoice->payments as $payment)
                                <div wire:key="payment-{{ $payment->id }}" class="flex items-center justify-between gap-3 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm text-gray-900 dark:text-white">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</p>
                                            <span class="px-1.5 py-0.5 text-xs font-medium rounded-full {{ $payment->status_approval->badgeClass() }}">
                                                {{ $payment->status_approval->label() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $payment->tanggal_bayar->format('d/m/Y') }}
                                            @if($payment->metode_bayar) • {{ $payment->metode_bayar }} @endif
                                        </p>
                                        @if($payment->status_approval->value === 'rejected' && $payment->rejection_reason)
                                            <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">Ditolak: {{ $payment->rejection_reason }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($payment->isProcessing())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $payment->file_status_enum->badgeClass() }}" title="File sedang diproses oleh sistem">
                                                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                {{ $payment->file_status_enum->label() }}
                                            </span>
                                        @elseif($payment->isFailed())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $payment->file_status_enum->badgeClass() }}" title="{{ $payment->file_error ?? 'Gagal memproses file' }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $payment->file_status_enum->label() }}
                                            </span>
                                        @elseif($payment->isCompleted())
                                            <button wire:click="downloadPaymentFile({{ $payment->id }})"
                                                wire:loading.attr="disabled" wire:target="downloadPaymentFile({{ $payment->id }})"
                                                class="text-blue-500 hover:text-blue-700 p-1 disabled:opacity-50" title="Download bukti transfer">
                                                <svg wire:loading.class="hidden" wire:target="downloadPaymentFile({{ $payment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <svg wire:loading wire:target="downloadPaymentFile({{ $payment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </button>
                                        @endif
                                        @if($payment->isPending())
                                            @can('approvePayment', [$pengadaan, $payment])
                                                <button wire:click="confirmApprovePayment({{ $payment->id }})"
                                                    wire:loading.attr="disabled" wire:target="confirmApprovePayment({{ $payment->id }})"
                                                    class="text-emerald-600 hover:text-emerald-800 p-1 disabled:opacity-50" title="Setujui pembayaran">
                                                    <svg wire:loading.class="hidden" wire:target="confirmApprovePayment({{ $payment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <svg wire:loading wire:target="confirmApprovePayment({{ $payment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </button>
                                                <button wire:click="confirmRejectPayment({{ $payment->id }})"
                                                    wire:loading.attr="disabled" wire:target="confirmRejectPayment({{ $payment->id }})"
                                                    class="text-orange-600 hover:text-orange-800 p-1 disabled:opacity-50" title="Tolak pembayaran">
                                                    <svg wire:loading.class="hidden" wire:target="confirmRejectPayment({{ $payment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <svg wire:loading wire:target="confirmRejectPayment({{ $payment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </button>
                                            @endcan
                                            @can('update', $pengadaan)
                                                <button wire:click="openEditPaymentModal({{ $invoice->id }}, {{ $payment->id }})"
                                                    wire:loading.attr="disabled" wire:target="openEditPaymentModal({{ $invoice->id }}, {{ $payment->id }})"
                                                    class="text-gray-500 hover:text-gray-700 p-1 disabled:opacity-50" title="Edit pembayaran">
                                                    <svg wire:loading.class="hidden" wire:target="openEditPaymentModal({{ $invoice->id }}, {{ $payment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <svg wire:loading wire:target="openEditPaymentModal({{ $invoice->id }}, {{ $payment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </button>
                                                <button wire:click="confirmDeletePayment({{ $payment->id }})"
                                                    wire:loading.attr="disabled" wire:target="confirmDeletePayment({{ $payment->id }})"
                                                    class="text-red-500 hover:text-red-700 p-1 disabled:opacity-50" title="Hapus pembayaran">
                                                    <svg wire:loading.class="hidden" wire:target="confirmDeletePayment({{ $payment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <svg wire:loading wire:target="confirmDeletePayment({{ $payment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 dark:text-gray-500">Belum ada pembayaran.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada invoice dari vendor.</p>
            </div>
        @endif
    </div>

    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row items-center justify-end gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-4">
        <x-cancel-button wire:click="goBack" target="goBack" wire:key="btn-back" variant="secondary" size="lg" class="w-full sm:w-auto">
            Kembali
        </x-cancel-button>
        @can('update', $pengadaan)
            <a href="{{ route('pengadaan.edit', $pengadaan) }}" wire:navigate
                x-data="{ loading: false }" @click="loading = true"
                wire:key="btn-edit-detail"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto">
                <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <svg x-show="loading" x-cloak class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Edit Pengadaan
            </a>
        @endcan
    </div>

    {{-- Modal: Tambah/Edit Invoice --}}
    @if($showInvoiceModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showInvoiceModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeInvoiceModal()"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="saveInvoice">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editingInvoiceId ? 'Edit Invoice' : 'Tambah Invoice' }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="inv_no_invoice" value="No. Invoice" :required="true" />
                                    <x-text-input wire:model="inv_no_invoice" id="inv_no_invoice" type="text" class="mt-1 block w-full" placeholder="Mis. INV-2026-001" />
                                    <x-input-error :messages="$errors->get('inv_no_invoice')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="inv_tanggal_invoice" value="Tanggal Invoice" :required="true" />
                                    <x-text-input wire:model="inv_tanggal_invoice" id="inv_tanggal_invoice" type="date" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('inv_tanggal_invoice')" class="mt-2" />
                                </div>
                                <div>
                                    <x-rupiah-input wire-model="inv_jumlah" label="Jumlah Tagihan" :required="true" id="inv_jumlah" />
                                    <x-input-error :messages="$errors->get('inv_jumlah')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="inv_jatuh_tempo" value="Jatuh Tempo" />
                                    <x-text-input wire:model="inv_jatuh_tempo" id="inv_jatuh_tempo" type="date" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('inv_jatuh_tempo')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="inv_catatan" value="Catatan" />
                                    <textarea wire:model="inv_catatan" id="inv_catatan" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm"></textarea>
                                    <x-input-error :messages="$errors->get('inv_catatan')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="inv_file" value="Dokumen Invoice" />
                                    @if($inv_file)
                                        <div class="mt-1 flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="text-xs text-blue-700 dark:text-blue-300 truncate flex-1">{{ $inv_file->getClientOriginalName() }}</span>
                                            <span class="text-xs text-blue-500 dark:text-blue-400">{{ number_format($inv_file->getSize() / 1024, 0) }} KB</span>
                                            <button type="button" wire:click="removeInvoiceFile" wire:loading.attr="disabled" wire:target="removeInvoiceFile" class="text-red-500 hover:text-red-700 p-0.5 disabled:opacity-50" title="Hapus file">
                                                <svg wire:loading.class="hidden" wire:target="removeInvoiceFile" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <svg wire:loading wire:target="removeInvoiceFile" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </button>
                                        </div>
                                    @else
                                        <x-file-dropzone wire-model="inv_file" id="inv_file" :helper-text="get_upload_config_display('invoice')" :accept="'.'.implode(',.', get_allowed_mimes_array('invoice'))" />
                                    @endif
                                    <x-input-error :messages="$errors->get('inv_file')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="saveInvoice" wire:key="btn-save-invoice" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                                {{ $editingInvoiceId ? 'Update' : 'Simpan' }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeInvoiceModal" target="closeInvoiceModal" wire:key="btn-cancel-invoice" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <x-delete-modal
        :show="$showDeleteInvoiceModal"
        wire:model="showDeleteInvoiceModal"
        title="Hapus Invoice"
        message="Apakah Anda yakin ingin menghapus invoice ini? Semua riwayat pembayaran terkait juga akan terhapus."
        confirmMethod="deleteInvoice"
    />

    {{-- Modal: Tambah/Edit Payment --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showPaymentModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closePaymentModal()"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="savePayment">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editingPaymentId ? 'Edit Pembayaran' : 'Tambah Pembayaran' }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="pay_tanggal_bayar" value="Tanggal Bayar" :required="true" />
                                    <x-text-input wire:model="pay_tanggal_bayar" id="pay_tanggal_bayar" type="date" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('pay_tanggal_bayar')" class="mt-2" />
                                </div>
                                <div>
                                    <x-rupiah-input wire-model="pay_jumlah_bayar" label="Jumlah Bayar" :required="true" id="pay_jumlah_bayar" />
                                    <x-input-error :messages="$errors->get('pay_jumlah_bayar')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="pay_metode_bayar" value="Metode Bayar" />
                                    <x-text-input wire:model="pay_metode_bayar" id="pay_metode_bayar" type="text" class="mt-1 block w-full" placeholder="Mis. Transfer Bank BCA" />
                                    <x-input-error :messages="$errors->get('pay_metode_bayar')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="pay_catatan" value="Catatan" />
                                    <textarea wire:model="pay_catatan" id="pay_catatan" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm"></textarea>
                                    <x-input-error :messages="$errors->get('pay_catatan')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="pay_file" value="Bukti Transfer" />
                                    @if($pay_file)
                                        <div class="mt-1 flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="text-xs text-blue-700 dark:text-blue-300 truncate flex-1">{{ $pay_file->getClientOriginalName() }}</span>
                                            <span class="text-xs text-blue-500 dark:text-blue-400">{{ number_format($pay_file->getSize() / 1024, 0) }} KB</span>
                                            <button type="button" wire:click="removePaymentFile" wire:loading.attr="disabled" wire:target="removePaymentFile" class="text-red-500 hover:text-red-700 p-0.5 disabled:opacity-50" title="Hapus file">
                                                <svg wire:loading.class="hidden" wire:target="removePaymentFile" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <svg wire:loading wire:target="removePaymentFile" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </button>
                                        </div>
                                    @else
                                        <x-file-dropzone wire-model="pay_file" id="pay_file" :helper-text="get_upload_config_display('invoice-payment')" :accept="'.'.implode(',.', get_allowed_mimes_array('invoice-payment'))" />
                                    @endif
                                    <x-input-error :messages="$errors->get('pay_file')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="savePayment" wire:key="btn-save-payment" variant="primary" size="lg" loadingText="Menyimpan..." class="w-full sm:w-auto">
                                {{ $editingPaymentId ? 'Update' : 'Simpan' }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closePaymentModal" target="closePaymentModal" wire:key="btn-cancel-payment" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <x-delete-modal
        :show="$showDeletePaymentModal"
        wire:model="showDeletePaymentModal"
        title="Hapus Pembayaran"
        message="Apakah Anda yakin ingin menghapus riwayat pembayaran ini?"
        confirmMethod="deletePayment"
    />

    {{-- Modal: Approve Payment --}}
    @if($showApprovePaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showApprovePaymentModal', false)"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="mt-3 text-left sm:mt-0 sm:ml-4 flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Setujui Pembayaran</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Apakah Anda yakin ingin menyetujui pembayaran ini? Nilai akan dihitung sebagai "sudah dibayar".</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="approvePayment"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed" wire:target="approvePayment">
                            <svg wire:loading wire:target="approvePayment" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Setujui
                        </button>
                        <x-cancel-button wire:click="$set('showApprovePaymentModal', false)" target="closeApprovePaymentModal" wire:key="btn-approve-payment-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Reject Payment --}}
    @if($showRejectPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.set('showRejectPaymentModal', false)"></div>
                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="rejectPayment">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tolak Pembayaran</h3>
                            <x-input-label for="pay_rejection_reason" value="Alasan Penolakan" :required="true" />
                            <textarea wire:model="pay_rejection_reason" id="pay_rejection_reason" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 px-3 py-2 text-sm" placeholder="Jelaskan alasan penolakan..."></textarea>
                            <x-input-error :messages="$errors->get('pay_rejection_reason')" class="mt-2" />
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed" wire:target="rejectPayment">
                                <svg wire:loading wire:target="rejectPayment" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Tolak
                            </button>
                            <x-cancel-button wire:click="$set('showRejectPaymentModal', false)" target="closeRejectPaymentModal" wire:key="btn-reject-payment-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
