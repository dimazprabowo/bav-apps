{{--
    Auth Branding Panel - Left/right side panel for guest auth pages.

    D-BONE context: pengadaan aset & vendor management for PT BKI.
    Visual: 5-photo diagonal collage as full-bleed background (clip-path polygons),
    blue gradient overlay for text readability. Photos: maritime equipment, port
    operations, and office/business (Unsplash, free license).
    Logo from app_logo_url() (configurable via System Configuration).
--}}
<div class="hidden lg:flex lg:w-1/2 xl:w-2/5 p-8 lg:p-12 flex-col justify-between relative overflow-hidden text-white">

    {{-- 5 Photo Layers (zigzag diagonal collage via clip-path) --}}
    {{-- Alternating slope direction: naik-kanan, turun-kanan, naik-kanan, turun-kanan --}}
    <div class="absolute inset-0" aria-hidden="true">
        {{-- Photo 1: top band (ship engine room) - slopes UP to right --}}
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/auth-photo-1.jpg') }}'); clip-path: polygon(0 0, 100% 0, 100% 8%, 0 32%);"></div>
        {{-- Photo 2: upper-middle band (port cranes) - slopes DOWN to right --}}
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/auth-photo-2.jpg') }}'); clip-path: polygon(0 32%, 100% 8%, 100% 52%, 0 28%);"></div>
        {{-- Photo 3: center band (cargo ship) - slopes UP to right --}}
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/auth-photo-3.jpg') }}'); clip-path: polygon(0 28%, 100% 52%, 100% 48%, 0 72%);"></div>
        {{-- Photo 4: lower-middle band (business meeting) - slopes DOWN to right --}}
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/auth-photo-4.jpg') }}'); clip-path: polygon(0 72%, 100% 48%, 100% 92%, 0 68%);"></div>
        {{-- Photo 5: bottom band (office teamwork) --}}
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/auth-photo-5.jpg') }}'); clip-path: polygon(0 68%, 100% 92%, 100% 100%, 0 100%);"></div>
    </div>

    {{-- Blue gradient overlay for brand consistency & text contrast --}}
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 via-blue-800/85 to-blue-900/90 dark:from-blue-900/95 dark:via-blue-900/90 dark:to-gray-900/95"></div>

    {{-- Subtle blueprint grid overlay --}}
    <div class="absolute inset-0 opacity-[0.05] pointer-events-none" aria-hidden="true">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="bp-grid" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="currentColor" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#bp-grid)"/>
        </svg>
    </div>

    {{-- Top accent line --}}
    <div class="absolute top-0 left-0 right-0 h-1 bg-blue-400/30"></div>

    {{-- Content --}}
    <div class="relative z-10 flex flex-col h-full justify-between">

        {{-- Top Section - Logo & Title --}}
        <div>
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center shadow-lg p-1.5 overflow-hidden flex-shrink-0">
                    <img src="{{ app_logo_url() }}" alt="BKI Logo" class="w-full h-full object-contain rounded-lg">
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold leading-tight">{{ app_name() }}</h1>
                    <p class="text-sm text-blue-200">PT. Biro Klasifikasi Indonesia</p>
                </div>
            </div>

            {{-- Main headline --}}
            <div class="space-y-3 max-w-lg mb-6">
                <h2 class="text-2xl lg:text-3xl xl:text-4xl font-bold leading-tight">
                    Pengadaan Aset & Vendor
                </h2>
                <p class="text-sm lg:text-base text-blue-100 leading-relaxed">
                    Sistem terintegrasi untuk pengadaan aset operasional, manajemen vendor,
                    penagihan invoice, pembayaran, dan monitoring biaya real-time.
                </p>
            </div>
        </div>

        {{-- Middle Section - Equipment Line-Art Illustration --}}
        <div class="flex-1 flex items-center justify-center my-6">
            <div class="relative w-full max-w-md">
                {{-- Professional equipment monitoring illustration --}}
                {{-- Layout: 4 equipment icons placed ON the inner dashed ring at 90° intervals
                     (cross arrangement: top/right/bottom/left), dashboard panel in center.
                     viewBox 400x400, center (200,200), inner ring r=140. --}}
                <svg class="relative w-full h-80 text-blue-100" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="screenGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.15"/>
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0.05"/>
                        </linearGradient>
                        <style>
                            {{-- Respect user motion preference for accessibility --}}
                            @media (prefers-reduced-motion: reduce) {
                                animate, animateTransform { display: none; }
                            }
                        </style>
                    </defs>

                    {{-- Outer decorative ring (solid) --}}
                    <circle cx="200" cy="200" r="175" stroke="currentColor" stroke-width="1" fill="none" opacity="0.15"/>
                    {{-- Inner decorative ring (dashed, slow rotation) --}}
                    <circle cx="200" cy="200" r="140" stroke="currentColor" stroke-width="1.5" fill="none" stroke-dasharray="6,6" opacity="0.25">
                        <animateTransform attributeName="transform" type="rotate" from="0 200 200" to="360 200 200" dur="80s" repeatCount="indefinite"/>
                    </circle>

                    {{-- Center: Monitoring Dashboard Panel --}}
                    <g transform="translate(200,200)">
                        <rect x="-80" y="-52" width="160" height="104" rx="8" stroke="currentColor" stroke-width="2.5" fill="url(#screenGrad)"/>
                        <rect x="-75" y="-47" width="150" height="94" rx="4" stroke="currentColor" stroke-width="0.5" fill="none" opacity="0.4"/>
                        <line x1="-75" y1="-37" x2="75" y2="-37" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                        <circle cx="-69" cy="-42" r="2" fill="currentColor" opacity="0.7"/>
                        <circle cx="-61" cy="-42" r="2" fill="currentColor" opacity="0.5"/>
                        <circle cx="-53" cy="-42" r="2" fill="currentColor" opacity="0.3"/>
                        <text x="0" y="-43" text-anchor="middle" font-size="9" fill="currentColor" opacity="0.7" font-family="monospace">MONITORING
                            <animate attributeName="opacity" values="0.7;0.4;0.7" dur="3s" repeatCount="indefinite"/>
                        </text>
                        {{-- Bar chart with animated bars (wave effect) --}}
                        <g transform="translate(-56,-4)">
                            <line x1="0" y1="34" x2="0" y2="0" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                            <line x1="0" y1="34" x2="48" y2="34" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                            <rect x="3" y="20" width="8" height="14" fill="currentColor" opacity="0.6">
                                <animate attributeName="height" values="14;20;14" dur="3s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                                <animate attributeName="y" values="20;14;20" dur="3s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                            </rect>
                            <rect x="15" y="12" width="8" height="22" fill="currentColor" opacity="0.7">
                                <animate attributeName="height" values="22;14;22" dur="3s" begin="0.4s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                                <animate attributeName="y" values="12;20;12" dur="3s" begin="0.4s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                            </rect>
                            <rect x="27" y="4" width="8" height="30" fill="currentColor" opacity="0.8">
                                <animate attributeName="height" values="30;22;30" dur="3s" begin="0.8s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                                <animate attributeName="y" values="4;12;4" dur="3s" begin="0.8s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                            </rect>
                            <rect x="39" y="16" width="8" height="18" fill="currentColor" opacity="0.6">
                                <animate attributeName="height" values="18;26;18" dur="3s" begin="1.2s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                                <animate attributeName="y" values="16;8;16" dur="3s" begin="1.2s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                            </rect>
                        </g>
                        {{-- Mini gauge with animated needle --}}
                        <g transform="translate(38,4)">
                            <path d="M-15,15 A15,15 0 0,1 15,15" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M-15,15 A15,15 0 0,1 7,3" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <line x1="0" y1="15" x2="7" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <animateTransform attributeName="transform" type="rotate" values="-12 0 15;12 0 15;-12 0 15" dur="3.5s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                            </line>
                            <circle cx="0" cy="15" r="2.5" fill="currentColor"/>
                        </g>
                        {{-- Status dots with pulse on warning indicators --}}
                        <g transform="translate(-56,34)">
                            <circle cx="0" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="12" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="24" cy="0" r="2.8" fill="#f59e0b">
                                <animate attributeName="opacity" values="1;0.3;1" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="36" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="48" cy="0" r="2.8" fill="#ef4444">
                                <animate attributeName="opacity" values="1;0.3;1" dur="1.8s" begin="0.5s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="60" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="72" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="84" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="96" cy="0" r="2.8" fill="#10b981"/>
                            <circle cx="108" cy="0" r="2.8" fill="#10b981"/>
                        </g>
                    </g>

                    {{-- 4 Equipment Icons ON the inner ring (r=140) at 90° intervals from top --}}
                    {{-- Position 0° (top): Wrench — subtle rotate wobble (tightening motion) --}}
                    <g transform="translate(200,60)" stroke="currentColor" stroke-width="3.5" fill="none" stroke-linejoin="round" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="rotate" values="-6;6;-6" dur="4s" repeatCount="indefinite" additive="sum" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                        <path d="M-26,-40 L-26,-12 L-10,4 L-7,12 L-7,42 L7,42 L7,12 L10,4 L26,-12 L26,-40 L16,-40 L16,-16 L-16,-16 L-16,-40 Z"/>
                        <line x1="-7" y1="26" x2="7" y2="26" stroke-width="2" opacity="0.4"/>
                        <line x1="-7" y1="34" x2="7" y2="34" stroke-width="2" opacity="0.4"/>
                    </g>

                    {{-- Position 90° (right): Crane — hook lowers and raises (loading motion) --}}
                    <g transform="translate(340,200)" stroke="currentColor" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round">
                        <line x1="-17" y1="42" x2="-17" y2="-42"/>
                        <line x1="17" y1="42" x2="17" y2="-42"/>
                        <line x1="-17" y1="-20" x2="17" y2="4" stroke-width="2" opacity="0.6"/>
                        <line x1="-17" y1="4" x2="17" y2="-20" stroke-width="2" opacity="0.6"/>
                        <line x1="-17" y1="20" x2="17" y2="38" stroke-width="2" opacity="0.6"/>
                        <line x1="-17" y1="38" x2="17" y2="20" stroke-width="2" opacity="0.6"/>
                        <line x1="-24" y1="-42" x2="54" y2="-42"/>
                        <line x1="-24" y1="-34" x2="54" y2="-34"/>
                        <line x1="-24" y1="-42" x2="-24" y2="-34"/>
                        <line x1="54" y1="-42" x2="54" y2="-34"/>
                        <line x1="-6" y1="-42" x2="6" y2="-34" stroke-width="2" opacity="0.6"/>
                        <line x1="-6" y1="-34" x2="6" y2="-42" stroke-width="2" opacity="0.6"/>
                        <line x1="24" y1="-42" x2="36" y2="-34" stroke-width="2" opacity="0.6"/>
                        <line x1="24" y1="-34" x2="36" y2="-42" stroke-width="2" opacity="0.6"/>
                        {{-- Hook cable (extends as hook lowers) --}}
                        <line x1="42" y1="-34" x2="42" y2="-8" stroke-width="2">
                            <animate attributeName="y2" values="-8;2;-8" dur="3.5s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                        </line>
                        {{-- Hook (translates down as cable extends) --}}
                        <path d="M36,-8 L42,0 L48,-8" fill="none" stroke-width="2.5">
                            <animateTransform attributeName="transform" type="translate" values="0,0;0,10;0,0" dur="3.5s" repeatCount="indefinite" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                        </path>
                        <line x1="-24" y1="42" x2="24" y2="42" stroke-width="5"/>
                    </g>

                    {{-- Position 180° (bottom): Ship — gentle bobbing (floating on water) --}}
                    <g transform="translate(200,340)" stroke="currentColor" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="translate" values="0,-3;0,3;0,-3" dur="3.5s" repeatCount="indefinite" additive="sum" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                        <path d="M-50,16 L50,16 L40,34 L-40,34 Z" fill="none"/>
                        <rect x="-33" y="-4" width="24" height="20" fill="none"/>
                        <rect x="-4" y="-23" width="17" height="39" fill="none"/>
                        <rect x="17" y="-12" width="22" height="27" fill="none"/>
                        <line x1="4" y1="-23" x2="4" y2="-43"/>
                        <line x1="-10" y1="-33" x2="18" y2="-33"/>
                        <path d="M-57,38 Q-47,34 -37,38 T-17,38 T3,38 T23,38 T43,38 T57,38" stroke-width="2" opacity="0.5"/>
                    </g>

                    {{-- Position 270° (left): Document — subtle scale pulse (being processed) --}}
                    <g transform="translate(60,200)" stroke="currentColor" stroke-width="3.5" fill="none" stroke-linejoin="round" stroke-linecap="round">
                        <animateTransform attributeName="transform" type="scale" values="1;1.05;1" dur="4s" repeatCount="indefinite" additive="sum" calcMode="spline" keyTimes="0;0.5;1" keySplines="0.4 0 0.6 1; 0.4 0 0.6 1"/>
                        <path d="M-27,-40 L16,-40 L32,-24 L32,40 L-27,40 Z"/>
                        <path d="M16,-40 L16,-24 L32,-24"/>
                        <line x1="-17" y1="-12" x2="22" y2="-12" stroke-width="2.5" opacity="0.6"/>
                        <line x1="-17" y1="0" x2="22" y2="0" stroke-width="2.5" opacity="0.6"/>
                        <line x1="-17" y1="12" x2="22" y2="12" stroke-width="2.5" opacity="0.6"/>
                        <text x="0" y="32" text-anchor="middle" font-size="18" fill="currentColor" opacity="0.8" font-family="monospace" stroke="none">Rp</text>
                    </g>
                </svg>
            </div>
        </div>

        {{-- Bottom Section - Key Capabilities --}}
        <div class="space-y-2.5">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-500/25 rounded-lg flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">Pengadaan Aset</h3>
                    <p class="text-xs text-blue-200">Pengadaan operasional dengan approval workflow</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-500/25 rounded-lg flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">Manajemen Vendor</h3>
                    <p class="text-xs text-blue-200">Database vendor, riwayat transaksi & performa</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-500/25 rounded-lg flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">Invoice & Pembayaran</h3>
                    <p class="text-xs text-blue-200">Penagihan, pembayaran & monitoring biaya real-time</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="relative z-10 text-blue-200 text-xs mt-4">
        <p>&copy; {{ date('Y') }} {{ app_name() }} &middot; PT. Biro Klasifikasi Indonesia</p>
    </div>
</div>
