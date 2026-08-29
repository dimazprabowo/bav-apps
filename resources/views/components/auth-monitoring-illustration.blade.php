{{--
    Industrial Monitoring Illustration for BAM Apps auth branding panel.

    Represents equipment/asset monitoring concepts: status dashboard,
    calibration gauge, inspection checklist, and monitoring network.

    Clean geometric line-art in white/blue tones — designed to sit on top
    of the blue gradient branding panel. ViewBox 0 0 480 360 (4:3).
    No clipping, no overflow, all shapes within viewBox bounds.
--}}
<svg class="w-full h-auto max-w-lg" viewBox="0 0 480 360" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Equipment monitoring dashboard illustration">
    {{-- Defs: grid pattern + gauge arc gradient --}}
    <defs>
        <pattern id="auth-grid" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.5" opacity="0.08"/>
        </pattern>
        <linearGradient id="auth-gauge-grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.4"/>
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0.9"/>
        </linearGradient>
    </defs>

    {{-- Blueprint grid background (subtle) --}}
    <rect width="480" height="360" fill="url(#auth-grid)"/>

    {{-- Main monitoring dashboard panel --}}
    <rect x="40" y="60" width="280" height="240" rx="12" fill="currentColor" fill-opacity="0.06" stroke="currentColor" stroke-opacity="0.25" stroke-width="1.5"/>

    {{-- Panel header bar --}}
    <rect x="40" y="60" width="280" height="36" rx="12" fill="currentColor" fill-opacity="0.1"/>
    <rect x="40" y="84" width="280" height="12" fill="currentColor" fill-opacity="0.1"/>
    <circle cx="58" cy="78" r="4" fill="currentColor" fill-opacity="0.5"/>
    <circle cx="72" cy="78" r="4" fill="currentColor" fill-opacity="0.5"/>
    <circle cx="86" cy="78" r="4" fill="currentColor" fill-opacity="0.5"/>
    <rect x="160" y="74" width="140" height="8" rx="4" fill="currentColor" fill-opacity="0.3"/>

    {{-- Status row 1 (operational - green dot) --}}
    <rect x="56" y="112" width="248" height="40" rx="6" fill="currentColor" fill-opacity="0.05"/>
    <circle cx="72" cy="132" r="6" fill="#34d399" fill-opacity="0.9"/>
    <rect x="88" y="126" width="120" height="6" rx="3" fill="currentColor" fill-opacity="0.5"/>
    <rect x="88" y="138" width="80" height="5" rx="2.5" fill="currentColor" fill-opacity="0.3"/>
    <rect x="260" y="128" width="32" height="12" rx="6" fill="#34d399" fill-opacity="0.25" stroke="#34d399" stroke-opacity="0.6" stroke-width="1"/>

    {{-- Status row 2 (calibration due - amber dot) --}}
    <rect x="56" y="160" width="248" height="40" rx="6" fill="currentColor" fill-opacity="0.05"/>
    <circle cx="72" cy="180" r="6" fill="#fbbf24" fill-opacity="0.9"/>
    <rect x="88" y="174" width="140" height="6" rx="3" fill="currentColor" fill-opacity="0.5"/>
    <rect x="88" y="186" width="96" height="5" rx="2.5" fill="currentColor" fill-opacity="0.3"/>
    <rect x="260" y="176" width="32" height="12" rx="6" fill="#fbbf24" fill-opacity="0.25" stroke="#fbbf24" stroke-opacity="0.6" stroke-width="1"/>

    {{-- Status row 3 (operational - green dot) --}}
    <rect x="56" y="208" width="248" height="40" rx="6" fill="currentColor" fill-opacity="0.05"/>
    <circle cx="72" cy="228" r="6" fill="#34d399" fill-opacity="0.9"/>
    <rect x="88" y="222" width="100" height="6" rx="3" fill="currentColor" fill-opacity="0.5"/>
    <rect x="88" y="234" width="72" height="5" rx="2.5" fill="currentColor" fill-opacity="0.3"/>
    <rect x="260" y="224" width="32" height="12" rx="6" fill="#34d399" fill-opacity="0.25" stroke="#34d399" stroke-opacity="0.6" stroke-width="1"/>

    {{-- Panel footer summary bar --}}
    <rect x="56" y="256" width="248" height="28" rx="6" fill="currentColor" fill-opacity="0.08"/>
    <rect x="68" y="266" width="60" height="6" rx="3" fill="currentColor" fill-opacity="0.4"/>
    <rect x="136" y="266" width="60" height="6" rx="3" fill="currentColor" fill-opacity="0.4"/>
    <rect x="204" y="266" width="88" height="6" rx="3" fill="currentColor" fill-opacity="0.4"/>

    {{-- Calibration gauge (right side) --}}
    <circle cx="380" cy="140" r="56" fill="currentColor" fill-opacity="0.06" stroke="currentColor" stroke-opacity="0.25" stroke-width="1.5"/>
    {{-- Gauge arc (270deg, from 135deg to 45deg) --}}
    <path d="M 340.2 179.8 A 56 56 0 1 1 419.8 179.8" fill="none" stroke="url(#auth-gauge-grad)" stroke-width="6" stroke-linecap="round"/>
    {{-- Gauge needle --}}
    <line x1="380" y1="140" x2="408" y2="112" stroke="currentColor" stroke-opacity="0.8" stroke-width="2.5" stroke-linecap="round"/>
    <circle cx="380" cy="140" r="5" fill="currentColor" fill-opacity="0.9"/>
    {{-- Gauge labels --}}
    <rect x="356" y="160" width="48" height="6" rx="3" fill="currentColor" fill-opacity="0.4"/>
    <rect x="364" y="172" width="32" height="5" rx="2.5" fill="currentColor" fill-opacity="0.25"/>

    {{-- Monitoring network nodes (bottom right) --}}
    <circle cx="380" cy="260" r="32" fill="currentColor" fill-opacity="0.05" stroke="currentColor" stroke-opacity="0.2" stroke-width="1.5"/>
    {{-- Connection lines from gauge to network --}}
    <path d="M 380 196 Q 380 220 380 228" stroke="currentColor" stroke-opacity="0.2" stroke-width="1.5" stroke-dasharray="3 3" fill="none"/>
    <path d="M 336 140 Q 300 200 348 260" stroke="currentColor" stroke-opacity="0.15" stroke-width="1.5" stroke-dasharray="3 3" fill="none"/>
    {{-- Network nodes --}}
    <circle cx="368" cy="252" r="5" fill="currentColor" fill-opacity="0.6"/>
    <circle cx="392" cy="252" r="5" fill="currentColor" fill-opacity="0.6"/>
    <circle cx="380" cy="272" r="5" fill="currentColor" fill-opacity="0.6"/>
    <line x1="368" y1="252" x2="392" y2="252" stroke="currentColor" stroke-opacity="0.3" stroke-width="1.5"/>
    <line x1="368" y1="252" x2="380" y2="272" stroke="currentColor" stroke-opacity="0.3" stroke-width="1.5"/>
    <line x1="392" y1="252" x2="380" y2="272" stroke="currentColor" stroke-opacity="0.3" stroke-width="1.5"/>

    {{-- Top-right corner accent: inspection checklist --}}
    <rect x="340" y="40" width="100" height="56" rx="8" fill="currentColor" fill-opacity="0.06" stroke="currentColor" stroke-opacity="0.2" stroke-width="1.5"/>
    <rect x="352" y="52" width="76" height="5" rx="2.5" fill="currentColor" fill-opacity="0.4"/>
    {{-- Checklist items with checkmarks --}}
    <path d="M 352 66 l 3 3 l 5 -6" stroke="#34d399" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <rect x="364" y="64" width="64" height="5" rx="2.5" fill="currentColor" fill-opacity="0.3"/>
    <path d="M 352 80 l 3 3 l 5 -6" stroke="#34d399" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <rect x="364" y="78" width="48" height="5" rx="2.5" fill="currentColor" fill-opacity="0.3"/>
</svg>
