@props([
    'show' => false,
    'title' => 'Hapus Data',
    'message' => 'Yakin ingin menghapus data ini?',
    'itemName' => '',
    'confirmMethod' => 'delete',
])

@if($show)
    <div class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80" wire:click="$set('{{ $attributes->wire('model')->value() }}', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-sm z-10 text-center">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">{{ $title }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ $message }}
                        @if($itemName)
                            <span class="font-semibold text-gray-700 dark:text-white">{{ $itemName }}</span>?
                        @endif
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="{{ $confirmMethod }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-base font-medium rounded-lg shadow-sm transition-all w-full sm:w-auto disabled:opacity-70 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                        wire:target="{{ $confirmMethod }}">
                        <svg wire:loading wire:target="{{ $confirmMethod }}" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Ya, Hapus
                    </button>
                    <x-cancel-button wire:click="$set('{{ $attributes->wire('model')->value() }}', false)" target="closeDeleteModal" wire:key="btn-delete-cancel" variant="secondary" size="lg" class="mt-3 sm:mt-0 w-full sm:w-auto" />
                </div>
            </div>
        </div>
    </div>
@endif
