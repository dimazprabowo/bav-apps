@props([
    'wireModel',          // Livewire property path, e.g. 'inv_file' or 'evidences.0.file'
    'label' => null,
    'required' => false,
    'id' => null,
    'accept' => null,     // e.g. '.pdf,.doc,.docx' or result of implode(',.', ...)
    'helperText' => null, // upload config display text
    'compact' => false,   // smaller padding for inline/repeater usage
])

@php
    $inputId = $id ?? 'dropzone-'.str_replace(['.', '_'], '-', $wireModel);
    $acceptAttr = $accept ?? '';
@endphp

<div
    x-data="{ dragging: false, uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false; progress = 0"
    x-on:livewire-upload-cancel="uploading = false; progress = 0"
    x-on:livewire-upload-error="uploading = false; progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    @if($label)
        <x-input-label :for="$inputId" :value="$label" :required="$required" />
    @endif

    {{-- The parent view is responsible for conditionally rendering the "file selected"
         state (with filename, size, and remove button). This component only renders
         the drag-and-drop dropzone area and upload progress. --}}

    <div
        x-show="!uploading"
        @dragover.prevent="dragging = true"
        @dragenter.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @dragend.prevent="dragging = false"
        @drop.prevent="
            dragging = false;
            if ($event.dataTransfer.files.length > 0) {
                let dt = new DataTransfer();
                dt.items.add($event.dataTransfer.files[0]);
                $refs.fileInput.files = dt.files;
                $refs.fileInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        "
        :class="dragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-blue-400 dark:hover:border-blue-500'"
        class="@if($label)mt-1 @endif flex flex-col items-center justify-center w-full @if($compact)px-3 py-3 @else px-4 py-6 @endif border-2 border-dashed rounded-lg cursor-pointer transition-colors"
    >
        <label for="{{ $inputId }}" class="flex flex-col items-center justify-center cursor-pointer w-full">
            <svg class="@if($compact)w-5 h-5 @else w-8 h-8 @endif text-gray-400 dark:text-gray-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="@if($compact)text-xs @else text-sm @endif text-gray-600 dark:text-gray-400 text-center">
                <span class="font-medium text-blue-600 dark:text-blue-400">Klik untuk pilih file</span>
                atau <span class="font-medium">drag &amp; drop</span> ke sini
            </p>
            @if($helperText)
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 text-center">{{ $helperText }}</p>
            @endif
        </label>
        <input
            type="file"
            x-ref="fileInput"
            id="{{ $inputId }}"
            wire:model="{{ $wireModel }}"
            class="hidden"
            @if($acceptAttr)accept="{{ $acceptAttr }}" @endif
        />
    </div>

    {{-- Upload Progress --}}
    <div
        x-show="uploading"
        x-cloak
        class="@if($label)mt-1 @endif flex items-center justify-center gap-3 w-full @if($compact)px-3 py-3 @else px-4 py-6 @endif border-2 border-blue-300 dark:border-blue-700 rounded-lg bg-blue-50 dark:bg-blue-900/20"
    >
        <svg class="animate-spin w-5 h-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <div class="flex-1">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Mengunggah...</span>
                <span class="text-xs text-blue-600 dark:text-blue-400" x-text="progress + '%'"></span>
            </div>
            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-1.5">
                <div class="bg-blue-600 dark:bg-blue-400 h-1.5 rounded-full transition-all" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    </div>

    @error($wireModel)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
