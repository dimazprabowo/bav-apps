@props(['alat'])

@if($alat->review_status->value !== 'pending')
    <div class="px-4 py-5 sm:p-6 border-t border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Review</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Reviewer</dt>
                <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->reviewer?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Tanggal Review</dt>
                <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
            @if($alat->approval_note)
                <div class="sm:col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Catatan Approval</dt>
                    <dd class="text-gray-900 dark:text-white mt-1">{{ $alat->approval_note }}</dd>
                </div>
            @endif
            @if($alat->rejection_reason)
                <div class="sm:col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Alasan Penolakan</dt>
                    <dd class="text-red-900 dark:text-red-400 mt-1">{{ $alat->rejection_reason }}</dd>
                </div>
            @endif
        </dl>
    </div>
@endif
