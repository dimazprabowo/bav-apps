@props([
    'wireModel',          // Livewire property path, e.g. 'npwp'
    'label' => null,
    'required' => false,
    'id' => null,
    'placeholder' => '00.000.000.0-000.000',
    'helperText' => null,
])

@php
    // Build unique id from wireModel if not provided
    $inputId = $id ?? 'npwp-'.str_replace(['.', '_'], '-', $wireModel);
@endphp

<div x-data="{
    display: '',
    formatNpwp(val) {
        // NPWP lama 15 digit: XX.XXX.XXX.X-XXX.XXX
        // NPWP baru 16 digit: XX.XXX.XXX.X-XXXX.XXX
        // Format mengikuti jumlah digit yang diinput (>12 digit = format 16 digit)
        let d = String(val ?? '').replace(/[^\d]/g, '').slice(0, 16);
        if (!d) return '';
        let long = d.length > 12;
        let out = d.slice(0, 2);
        if (d.length > 2) out += '.' + d.slice(2, 5);
        if (d.length > 5) out += '.' + d.slice(5, 8);
        if (d.length > 8) out += '.' + d.slice(8, 9);
        if (d.length > 9) out += '-' + (long ? d.slice(9, 13) : d.slice(9, 12));
        let rest = long ? d.slice(13, 16) : d.slice(12, 15);
        if (rest) out += '.' + rest;
        return out;
    },
    syncFromLivewire() {
        let path = '{{ $wireModel }}'.split('.');
        let val = $wire;
        for (let p of path) {
            val = val?.[p];
        }
        this.display = this.formatNpwp(val);
    },
    onInput(e) {
        this.display = this.formatNpwp(e.target.value);
        e.target.value = this.display;
        $wire.set('{{ $wireModel }}', this.display);
        clearTimeout(this._commitTimer);
        this._commitTimer = setTimeout(() => $wire.$commit(), 350);
    }
}" x-init="syncFromLivewire()" x-effect="syncFromLivewire()">
    @if($label)
        <x-input-label :for="$inputId" :value="$label" :required="$required" />
    @endif
    <input
        type="text"
        id="{{ $inputId }}"
        :value="display"
        x-on:input="onInput($event)"
        inputmode="numeric"
        placeholder="{{ $placeholder }}"
        class="@if($label)mt-1 @endif block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
        @if($required)required @endif
    />
    @if($helperText)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $helperText }}</p>
    @endif
</div>
