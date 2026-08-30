{{-- Partial: Dashboard Statistics View (requires dashboard_view permission) --}}
<div class="space-y-6">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Selamat Datang, {{ $authUser->name }}!</h2>
                <p class="mt-1 text-blue-100 text-sm">
                    {{ config('app.name') }} &mdash; Panel Administrasi
                </p>
                <div class="mt-3 inline-flex items-center whitespace-nowrap gap-2 bg-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                    {{ $authUserRole }}
                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-white opacity-20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Stat Cards: User & Role (gated by respective entity permissions) --}}
    @canany(['users_view', 'roles_view'])
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @can('users_view')
        {{-- Total Users --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Pengguna</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Pengguna terdaftar</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('roles_view')
        {{-- Total Roles --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Role</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRoles) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Role yang dikonfigurasi</p>
                </div>
                <div class="p-3 bg-violet-50 dark:bg-violet-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

    </div>
    @endcanany

    {{-- Kondisi Peralatan (gated by alat_view; cabang-scoped via service) --}}
    @can('alat_view')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kondisi Peralatan</h3>
            <a href="{{ route('master-data.alat.index') }}" wire:navigate
               class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                Lihat detail &rarr;
            </a>
        </div>

        {{-- Total + 3 grup kondisi (Baik / Rusak / Hilang) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total Alat --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Alat</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($alatStats['total']) }}</p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Seluruh peralatan</p>
            </div>

            {{-- Baik --}}
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-xs font-medium text-green-600 dark:text-green-400">Baik</p>
                <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($alatStats['kondisi_baik']) }}</p>
                <p class="mt-0.5 text-xs text-green-500/80 dark:text-green-500/60">Layak pakai</p>
            </div>

            {{-- Rusak (Ringan + Berat digabung) --}}
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <p class="text-xs font-medium text-red-600 dark:text-red-400">Rusak</p>
                <p class="mt-1 text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($alatStats['kondisi_rusak']) }}</p>
                <p class="mt-0.5 text-xs text-red-500/80 dark:text-red-500/60">
                    R. Ringan {{ $alatStats['kondisi_rusak_ringan'] }} &middot; R. Berat {{ $alatStats['kondisi_rusak_berat'] }}
                </p>
            </div>

            {{-- Hilang --}}
            <div class="p-4 bg-gray-100 dark:bg-gray-700/70 rounded-lg">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Hilang</p>
                <p class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-200">{{ number_format($alatStats['kondisi_hilang']) }}</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tidak ditemukan</p>
            </div>

        </div>
    </div>

    {{-- Status Kalibrasi (gated by alat_view; cabang-scoped via service) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Kalibrasi Expired --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kalibrasi Expired</p>
                    <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($alatStats['calibration_expired']) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Sudah jatuh tempo</p>
                </div>
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Kalibrasi Jatuh Tempo --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kalibrasi Jatuh Tempo</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($alatStats['calibration_pending']) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">&le; 30 hari ke depan</p>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Alat per Cabang (HANYA untuk user dengan akses seluruh cabang) --}}
    @can('access_all_cabang')
    @if(!empty($alatPerCabang))
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Distribusi Alat per Cabang</h3>
            <a href="{{ route('master-data.alat.index') }}" wire:navigate
               class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                Lihat detail &rarr;
            </a>
        </div>

        {{-- Card stats per cabang (responsive grid) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($alatPerCabang as $row)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $row['cabang'] }}</p>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ $row['total'] }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>{{ $row['baik'] }} Baik
                        </span>
                        <span class="flex items-center gap-1 text-red-600 dark:text-red-400">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>{{ $row['rusak'] }} Rusak
                        </span>
                        <span class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>{{ $row['hilang'] }} Hilang
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ApexCharts horizontal bar chart (nama cabang di Y-axis, jumlah di X-axis) --}}
        <div id="alat-per-cabang-chart" class="w-full" style="min-height: 350px;"></div>
    </div>
    @endif
    @endcan
    @endcan

    {{-- Quick Actions (only render if user has at least one relevant permission) --}}
    @canany(['users_view', 'roles_view', 'configuration_view'])
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

            @can('users_view')
            <a href="{{ route('settings.users') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Manajemen User</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kelola akun pengguna</p>
                </div>
            </a>
            @endcan

            @can('roles_view')
            <a href="{{ route('settings.roles') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-violet-400 dark:hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-violet-100 dark:bg-violet-900/30 rounded-lg group-hover:bg-violet-200 dark:group-hover:bg-violet-900/50 transition-colors">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Roles & Permissions</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kelola hak akses</p>
                </div>
            </a>
            @endcan

            @can('configuration_view')
            <a href="{{ route('settings.system') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Konfigurasi System</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pengaturan aplikasi</p>
                </div>
            </a>
            @endcan

        </div>
    </div>
    @endcanany

    {{-- System Info (gated by configuration_view — internal app info) --}}
    @can('configuration_view')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Informasi System</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Laravel {{ app()->version() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Framework</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">PHP {{ phpversion() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Runtime</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('app.env') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Environment</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('app.timezone') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Timezone</p>
                </div>
            </div>
        </div>
    </div>
    @endcan

</div>

@push('scripts')
@can('access_all_cabang')
@if(!empty($alatPerCabang))
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script>
    // Render chart — guard terhadap race condition DOMContentLoaded + CDN load lambat.
    // Pakai IIFE + polling agar chart selalu muncul meski:
    //   - DOMContentLoaded sudah fired (saat wire:navigate re-render)
    //   - ApexCharts CDN belum selesai di-load saat script pertama jalan
    (function () {
        const elId = 'alat-per-cabang-chart';
        let pollAttempts = 0;
        const MAX_POLL = 50; // 50 x 100ms = 5s timeout

        function renderChart() {
            const el = document.getElementById(elId);
            if (!el || typeof ApexCharts === 'undefined') {
                // ApexCharts belum ready atau element belum render — retry.
                if (pollAttempts++ < MAX_POLL) {
                    setTimeout(renderChart, 100);
                }
                return;
            }

            // Guard: jangan render dua kali di element yang sama (Livewire re-render).
            if (el.dataset.chartRendered === '1') return;
            el.dataset.chartRendered = '1';

            const data = @json($alatPerCabang ?? []);
            // Prepend nomor urut ke nama cabang (mis. "1. Cabang Utama...") sebagai penanda baris.
            // Tooltip x.formatter strip prefix "N. " agar tetap tampil nama full.
            const categories = data.map((r, i) => (i + 1) + '. ' + r.cabang);
            // Render tipis untuk value 0 (agar placeholder bar tetap terlihat).
            // Tooltip formatter akan tampilkan nilai asli (0), bukan epsilon.
            const EPSILON = 0.01;
            const baikData = data.map(r => Math.max(r.baik, EPSILON));
            const rusakData = data.map(r => Math.max(r.rusak, EPSILON));
            const hilangData = data.map(r => Math.max(r.hilang, EPSILON));
            const isEmpty = data.length === 0;

            // Skala integer: tickAmount = maxValue agar tick di interval bulat (0,1,2,...,max).
            // Tanpa ini, forceNiceScale generate tick 0.5 → setelah round jadi duplikat (0,0,1,1,...).
            const maxValue = isEmpty ? 10 : Math.max(...data.map(r => r.total), 1);

            const isDark = document.documentElement.classList.contains('dark');

            // Horizontal bar: nama cabang di Y-axis, jumlah di X-axis.
            // Cocok untuk banyak kategori (18 cabang) dengan nama panjang.
            // Tinggi dinamis: min 350px, +90px per kategori di atas 10 (slot tinggi → bar tebal + gap jelas).
            const chartHeight = Math.max(350, 350 + Math.max(0, categories.length - 10) * 90);

            const options = {
                series: [
                    { name: 'Baik', data: baikData, color: '#10b981' },
                    { name: 'Rusak', data: rusakData, color: '#ef4444' },
                    { name: 'Hilang', data: hilangData, color: '#9ca3af' },
                ],
                chart: {
                    type: 'bar',
                    height: chartHeight,
                    stacked: false,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent',
                    foreColor: isDark ? '#d1d5db' : '#4b5563',
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        // barHeight 80%: bar tebal (slot tinggi) + gap jelas antar kategori (20%).
                        barHeight: '80%',
                        borderRadius: 4,
                        borderRadiusApplication: 'end',
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: false },
                states: {
                    hover: { filter: { type: 'none' } },
                    active: { filter: { type: 'none' } },
                },
                xaxis: {
                    // Horizontal bar: categories di xaxis.categories (ApexCharts auto-swap ke y-axis visual).
                    // Tapi xaxis JUGA = value axis (sumbu angka di bawah) → min/max/tickAmount di sini.
                    categories: categories,
                    labels: {
                        style: { colors: isDark ? '#d1d5db' : '#4b5563', fontSize: '12px' },
                        // Value axis = jumlah barang → integer (cegah desimal 1.5, 2.5).
                        formatter: (val) => Math.round(val),
                    },
                    title: {
                        text: 'Jumlah Alat',
                        style: { color: isDark ? '#d1d5db' : '#4b5563', fontSize: '12px', fontWeight: 600 },
                    },
                    crosshairs: { show: false },
                    // tickAmount = maxValue → tick di interval bulat (0,1,2,...,max). Tanpa 0.5 step.
                    min: 0,
                    max: maxValue,
                    tickAmount: maxValue,
                },
                yaxis: {
                    labels: {
                        // Rata kiri agar nama cabang mulai dari kiri (profesional, mudah baca).
                        align: 'left',
                        // maxWidth luas agar nama cabang panjang tampil optimal (trim hanya untuk yg ekstrem).
                        maxWidth: 220,
                        style: { colors: isDark ? '#d1d5db' : '#4b5563', fontSize: '12px' },
                        // Category axis = "N. nama cabang". Trim hanya jika > 45 char, tooltip tetap full.
                        formatter: (val) => {
                            const str = String(val ?? '');
                            return str.length > 45 ? str.slice(0, 42) + '...' : str;
                        },
                    },
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: isDark ? '#d1d5db' : '#4b5563' },
                    markers: { width: 10, height: 10, radius: 5 },
                },
                fill: { opacity: 1 },
                tooltip: {
                    intersect: true,
                    shared: false,
                    x: {
                        // Strip prefix "N. " dari category ber-nomor → tampilkan nama cabang full.
                        formatter: (val) => String(val ?? '').replace(/^\d+\.\s/, ''),
                    },
                    y: {
                        formatter: (val) => {
                            // Tampilkan nilai asli (0), bukan epsilon yang dipakai untuk render tipis.
                            const actual = val < 1 ? 0 : Math.round(val);
                            return actual + ' alat';
                        },
                    },
                    theme: isDark ? 'dark' : 'light',
                },
                noData: {
                    text: 'Belum ada data alat',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: isDark ? '#9ca3af' : '#6b7280',
                        fontSize: '14px',
                        fontFamily: 'inherit',
                    },
                },
                grid: { borderColor: isDark ? '#374151' : '#e5e7eb', strokeDashArray: 4 },
            };

            const chart = new ApexCharts(el, options);
            chart.render();

            // Re-render on dark mode toggle via MutationObserver on <html> class
            const observer = new MutationObserver(() => {
                const nowDark = document.documentElement.classList.contains('dark');
                chart.updateOptions({
                    chart: { foreColor: nowDark ? '#d1d5db' : '#4b5563' },
                    xaxis: {
                        labels: {
                            style: { colors: nowDark ? '#d1d5db' : '#4b5563' },
                            formatter: (val) => Math.round(val),
                        },
                        title: { style: { color: nowDark ? '#d1d5db' : '#4b5563' } },
                    },
                    yaxis: {
                        labels: {
                            style: { colors: nowDark ? '#d1d5db' : '#4b5563' },
                            formatter: (val) => {
                                const str = String(val ?? '');
                                return str.length > 45 ? str.slice(0, 42) + '...' : str;
                            },
                        },
                    },
                    legend: { labels: { colors: nowDark ? '#d1d5db' : '#4b5563' } },
                    tooltip: { theme: nowDark ? 'dark' : 'light' },
                    grid: { borderColor: nowDark ? '#374151' : '#e5e7eb' },
                });
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }

        // Jalankan: langsung jika DOM sudah ready, atau tunggu DOMContentLoaded.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderChart);
        } else {
            renderChart();
        }
    })();
</script>
@endif
@endcan
@endpush
