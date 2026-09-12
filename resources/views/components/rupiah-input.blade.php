@props([
    'wireModel',          // Livewire property path, e.g. 'inv_jumlah' or 'items.0.harga_satuan'
    'label' => null,
    'required' => false,
    'id' => null,
    'placeholder' => '0',
    'helperText' => null,
])

@php
    // Build unique id from wireModel if not provided
    $inputId = $id ?? 'rupiah-'.str_replace(['.', '_'], '-', $wireModel);
@endphp

<div x-data="{
    display: '',
    formatRupiah(val) {
        let num = String(val ?? '').replace(/[^\d]/g, '');
        if (!num || parseInt(num) === 0) return '';
        return new Intl.NumberFormat('id-ID').format(parseInt(num));
    },
    syncFromLivewire() {
        let path = '{{ $wireModel }}'.split('.');
        let val = $wire;
        for (let p of path) {
            val = val?.[p];
        }
        this.display = this.formatRupiah(val);
    },
    onInput(e) {
        let num = String(e.target.value).replace(/[^\d]/g, '');
        let clean = num ? String(parseInt(num)) : '';
        this.display = this.formatRupiah(clean);
        $wire.set('{{ $wireModel }}', clean);
        e.target.value = this.display;
    }
}" x-init="syncFromLivewire()" x-effect="syncFromLivewire()">
    @if($label)
        <x-input-label :for="$inputId" :value="$label" :required="$required" />
    @endif
    <div class="@if($label)mt-1 @endif relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 pointer-events-none select-none">Rp</span>
        <input
            type="text"
            id="{{ $inputId }}"
            :value="display"
            x-on:input="onInput($event)"
            inputmode="numeric"
            placeholder="{{ $placeholder }}"
            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg pl-9 pr-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
            @if($required)required @endif
        />
    </div>
    @if($helperText)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $helperText }}</p>
    @endif
</div>
