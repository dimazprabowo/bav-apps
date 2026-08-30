@props(['title' => null])

@php
    // Auth Shell - Shared layout for all guest/authentication pages.
    // Reads panel position from the configuration layer (auth_layout_position()),
    // NEVER directly from env. Supports: left | center | right.
    // Mobile (<lg) always stacks vertically. Invalid values fall back to "center".
    $position = auth_layout_position();
    $showBranding = $position !== 'center';
    // Desktop row direction: default flex-row (left), reverse for right
    $rowDirection = $position === 'right' ? 'lg:flex-row-reverse' : 'lg:flex-row';
@endphp

<div class="min-h-screen flex flex-col {{ $rowDirection }} relative">
    {{-- Dark Mode Toggle (fixed, consistent across all auth pages) --}}
    <div class="fixed top-4 right-4 z-50">
        <button @click="$store.darkMode.toggle()"
                class="p-3 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 dark:border-gray-700"
                aria-label="Toggle dark mode">
            <svg x-show="!$store.darkMode.dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.darkMode.dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

    {{-- Branding Panel (desktop only, hidden when position=center) --}}
    @if($showBranding)
        <x-auth-branding-panel />
    @endif

    {{-- Form Panel --}}
    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen lg:min-h-0 relative overflow-hidden">
        {{-- Mobile Background (visible only on mobile, subtle brand gradient) --}}
        <div class="lg:hidden absolute inset-0 z-0 bg-gradient-to-br from-blue-700 via-blue-800 to-blue-900"></div>

        <div class="w-full max-w-md relative z-10">
            {{-- Mobile Logo (compact branding header for mobile) --}}
            <x-auth-mobile-logo />

            {{-- Auth Card (consistent styling across all pages) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700">
                {{ $slot }}
            </div>

            {{-- Footer Help Link (consistent across all pages) --}}
            {{-- Mobile: white/blue-100 on blue gradient bg; Desktop: gray on gray-50/900 bg --}}
            <p class="text-center text-sm text-white lg:text-gray-600 dark:text-blue-100 dark:lg:text-gray-400 mt-6">
                Butuh bantuan? <a href="#" class="text-blue-100 lg:text-blue-600 dark:lg:text-blue-400 hover:text-white dark:hover:text-white dark:lg:hover:text-blue-300 lg:hover:text-blue-700 font-medium transition-colors">Hubungi Support</a>
            </p>

            {{-- Mobile Footer Copyright (only mobile, sits on blue gradient bg) --}}
            <p class="lg:hidden text-center text-blue-200 text-xs mt-8">
                &copy; {{ date('Y') }} {{ app_name() }}. All rights reserved.
            </p>
        </div>
    </div>
</div>
