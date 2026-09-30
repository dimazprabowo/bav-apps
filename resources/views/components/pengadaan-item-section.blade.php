@props(['items', 'totalBiaya' => 0, 'satuanOptions' => []])

<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            Item Aset
            <span class="ml-2 inline-flex items-center whitespace-nowrap px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                {{ count($items) }} item
            </span>
        </h3>
        <button type="button" wire:click="addItem" wire:key="add-item-btn"
            wire:loading.attr="disabled" wire:target="addItem"
            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors disabled:opacity-50">
            <svg wire:loading.remove wire:target="addItem" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg wire:loading wire:target="addItem" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove wire:target="addItem">Tambah Item</span>
        </button>
    </div>

    @if(count($items) > 0)
        <div class="space-y-4">
            @foreach($items as $index => $item)
                <div wire:key="pengadaan-item-{{ $item['id'] ?? $index }}" class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Item #{{ $index + 1 }}</span>
                        @if(count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $index }})"
                                wire:loading.attr="disabled" wire:target="removeItem({{ $index }})"
                                class="text-red-400 hover:text-red-600 p-1 disabled:opacity-50" title="Hapus item">
                                <svg wire:loading.class="hidden" wire:target="removeItem({{ $index }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <svg wire:loading wire:target="removeItem({{ $index }})" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="items.{{ $index }}.nama_item" value="Nama Item" :required="true" />
                            <x-text-input wire:model="items.{{ $index }}.nama_item" type="text" class="mt-1 block w-full" placeholder="Mis. Multimeter Digital" />
                            <x-input-error :messages="$errors->get('items.'.$index.'.nama_item')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="items.{{ $index }}.kategori_item" value="Kategori Item" />
                            <x-text-input wire:model="items.{{ $index }}.kategori_item" type="text" class="mt-1 block w-full" placeholder="Mis. Alat Ukur" />
                            <x-input-error :messages="$errors->get('items.'.$index.'.kategori_item')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="items.{{ $index }}.qty" value="Qty" :required="true" />
                            <x-text-input wire:model.live.debounce.400ms="items.{{ $index }}.qty" type="number" min="1" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('items.'.$index.'.qty')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="items.{{ $index }}.satuan_id" value="Satuan" :required="true" />
                            <x-searchable-select wire:model="items.{{ $index }}.satuan_id"
                                :options="$satuanOptions"
                                placeholder="Pilih satuan"
                                searchPlaceholder="Cari satuan..."
                                wire:key="satuan-select-{{ $item['id'] ?? 'new-'.$index }}" />
                            <x-input-error :messages="$errors->get('items.'.$index.'.satuan_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-rupiah-input wire-model="items.{{ $index }}.harga_satuan" label="Harga Satuan" :required="true" id="items-{{ $index }}-harga_satuan" />
                            <x-input-error :messages="$errors->get('items.'.$index.'.harga_satuan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label value="Subtotal" />
                            <p class="mt-1 block w-full px-3 py-2.5 text-sm font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                Rp {{ number_format((int)($item['qty'] ?? 0) * (float)($item['harga_satuan'] ?? 0), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4 flex items-center justify-end gap-2 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <span class="text-sm font-medium text-blue-900 dark:text-blue-300">Total Biaya:</span>
        <span class="text-lg font-bold text-blue-900 dark:text-blue-300">Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}</span>
    </div>
</div>
