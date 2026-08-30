<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Status Kalibrasi Alat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            padding: 20px;
            line-height: 1.6;
            color: #1f2937;
        }

        .email-container {
            max-width: 680px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
        }

        {{-- Header: gradient biru profesional (digest, bukan per-type) --}}
        .header {
            padding: 36px 30px;
            text-align: center;
            color: #ffffff;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .logo {
            width: 72px;
            height: 72px;
            background-color: #ffffff;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 10px;
        }
        .logo img { width: 100%; height: 100%; object-fit: contain; }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .header p { font-size: 13px; opacity: 0.95; font-weight: 400; }

        .content { padding: 36px 30px; }

        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .message {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 28px;
            line-height: 1.7;
        }

        {{-- Summary cards: 3 kartu statistik --}}
        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 32px;
        }
        .summary-card {
            display: table-cell;
            width: 33.33%;
            border-radius: 10px;
            padding: 18px 12px;
            text-align: center;
            vertical-align: middle;
        }
        .summary-card-green {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
        }
        .summary-card-amber {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
        }
        .summary-card-red {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
        }
        .summary-card .count {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
        }
        .summary-card-green .count { color: #047857; }
        .summary-card-amber .count { color: #b45309; }
        .summary-card-red .count { color: #b91c1c; }
        .summary-card .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b7280;
        }

        {{-- Section block --}}
        .section { margin-bottom: 28px; }
        .section-header {
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 14px;
        }
        .section-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .section-dot-green { background-color: #10b981; }
        .section-dot-amber { background-color: #f59e0b; }
        .section-dot-red { background-color: #ef4444; }
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            flex-grow: 1;
        }
        .section-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
        }
        .section-badge-green { background-color: #d1fae5; color: #047857; }
        .section-badge-amber { background-color: #fef3c7; color: #b45309; }
        .section-badge-red { background-color: #fee2e2; color: #b91c1c; }

        {{-- Info line untuk count-only section --}}
        .info-line {
            font-size: 14px;
            color: #4b5563;
            padding: 14px 16px;
            background-color: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #10b981;
        }
        .info-line strong { color: #047857; font-weight: 700; }

        {{-- Alat list table --}}
        .alat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .alat-table thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .alat-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
            vertical-align: top;
        }
        .alat-table tbody tr:last-child td { border-bottom: none; }
        .alat-code { font-weight: 600; color: #1f2937; white-space: nowrap; }
        .alat-name { color: #4b5563; }
        .alat-cabang { font-size: 12px; color: #6b7280; }
        .alat-date-critical { color: #b91c1c; font-weight: 700; }
        .alat-date-warning { color: #b45309; font-weight: 600; }

        {{-- Empty state --}}
        .empty-state {
            padding: 18px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            background-color: #f9fafb;
            border-radius: 8px;
        }

        {{-- Action button --}}
        .button-container { text-align: center; margin: 32px 0 8px; }
        .action-button {
            display: inline-block;
            padding: 14px 36px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        {{-- Footer --}}
        .footer {
            background-color: #f9fafb;
            padding: 26px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p { font-size: 12px; color: #6b7280; margin-bottom: 6px; }
        .footer-links { margin-top: 12px; }
        .footer-links a {
            color: #2563eb;
            text-decoration: none;
            font-size: 12px;
            margin: 0 8px;
        }
        .footer-links a:hover { text-decoration: underline; }

        @media only screen and (max-width: 600px) {
            body { padding: 12px 8px; }
            .header { padding: 28px 20px; }
            .header h1 { font-size: 19px; }
            .content { padding: 24px 18px; }
            .summary-grid { display: block; border-spacing: 0; }
            .summary-card { display: block; width: 100%; margin-bottom: 10px; }
            .action-button { padding: 13px 28px; font-size: 14px; display: block; }
            .alat-table thead { display: none; }
            .alat-table, .alat-table tbody, .alat-table tr, .alat-table td { display: block; width: 100%; }
            .alat-table tr { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 8px; padding: 8px; }
            .alat-table td { border-bottom: none; padding: 4px 8px; }
            .footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="header">
            <div class="logo">
                <img src="{{ email_logo_url() }}" alt="BKI Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>Ringkasan Status Kalibrasi Alat</h1>
            <p>{{ config('app.name') }} &middot; Periode {{ now()->format('d M Y') }}</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <div class="greeting">Halo, {{ $userName }}</div>
            <div class="message">
                Berikut ringkasan status kalibrasi alat yang dikelola cabang Anda.
                Mohon perhatikan alat yang <strong>akan kadaluarsa</strong> dan <strong>sudah kadaluarsa</strong> untuk segera ditindaklanjuti.
            </div>

            {{-- Summary cards: 3 statistik utama --}}
            <div class="summary-grid">
                <div class="summary-card summary-card-green">
                    <div class="count">{{ $terkalibrasiCount }}</div>
                    <div class="label">Terkalibrasi</div>
                </div>
                <div class="summary-card summary-card-amber">
                    <div class="count">{{ $pendingCount }}</div>
                    <div class="label">Akan Kadaluarsa</div>
                </div>
                <div class="summary-card summary-card-red">
                    <div class="count">{{ $expiredCount }}</div>
                    <div class="label">Sudah Kadaluarsa</div>
                </div>
            </div>

            {{-- Section 1: Terkalibrasi (count only) --}}
            <div class="section">
                <div class="section-header">
                    <span class="section-dot section-dot-green"></span>
                    <span class="section-title">Terkalibrasi</span>
                    <span class="section-badge section-badge-green">{{ $terkalibrasiCount }} alat</span>
                </div>
                <div class="info-line">
                    <strong>{{ $terkalibrasiCount }} alat</strong> dalam kondisi kalibrasi valid (jatuh tempo masih &gt; {{ $thresholdDays }} hari ke depan).
                </div>
            </div>

            {{-- Section 2: Akan Kadaluarsa (count + list) --}}
            <div class="section">
                <div class="section-header">
                    <span class="section-dot section-dot-amber"></span>
                    <span class="section-title">Akan Kadaluarsa</span>
                    <span class="section-badge section-badge-amber">{{ $pendingCount }} alat</span>
                </div>
                @if(!empty($pendingList))
                    <table class="alat-table">
                        <thead>
                            <tr>
                                <th style="width: 18%">Kode</th>
                                <th style="width: 32%">Nama Alat</th>
                                <th style="width: 22%">Cabang</th>
                                <th style="width: 28%">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingList as $alat)
                                <tr>
                                    <td class="alat-code">{{ $alat['code'] }}</td>
                                    <td>
                                        <div class="alat-name">{{ $alat['name'] }}</div>
                                        @if(!empty($alat['merk_type']))
                                            <div class="alat-cabang">{{ $alat['merk_type'] }}</div>
                                        @endif
                                    </td>
                                    <td class="alat-cabang">{{ $alat['cabang'] }}</td>
                                    <td class="alat-date-warning">{{ $alat['tanggal_kalibrasi_berikutnya'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">Tidak ada alat yang akan kadaluarsa dalam {{ $thresholdDays }} hari ke depan.</div>
                @endif
            </div>

            {{-- Section 3: Sudah Kadaluarsa (count + list) --}}
            <div class="section">
                <div class="section-header">
                    <span class="section-dot section-dot-red"></span>
                    <span class="section-title">Sudah Kadaluarsa</span>
                    <span class="section-badge section-badge-red">{{ $expiredCount }} alat</span>
                </div>
                @if(!empty($expiredList))
                    <table class="alat-table">
                        <thead>
                            <tr>
                                <th style="width: 18%">Kode</th>
                                <th style="width: 32%">Nama Alat</th>
                                <th style="width: 22%">Cabang</th>
                                <th style="width: 28%">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiredList as $alat)
                                <tr>
                                    <td class="alat-code">{{ $alat['code'] }}</td>
                                    <td>
                                        <div class="alat-name">{{ $alat['name'] }}</div>
                                        @if(!empty($alat['merk_type']))
                                            <div class="alat-cabang">{{ $alat['merk_type'] }}</div>
                                        @endif
                                    </td>
                                    <td class="alat-cabang">{{ $alat['cabang'] }}</td>
                                    <td class="alat-date-critical">{{ $alat['tanggal_kalibrasi_berikutnya'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">Tidak ada alat dengan kalibrasi sudah kadaluarsa.</div>
                @endif
            </div>

            {{-- CTA --}}
            <div class="button-container">
                <a href="{{ $alatUrl }}" class="action-button">Lihat Detail Alat</a>
            </div>

            <div class="message" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 12px; color: #6b7280;">
                    Email ini dikirim secara otomatis oleh sistem sebagai ringkasan periodik status kalibrasi alat.
                    Mohon tidak membalas email ini. Untuk bantuan, hubungi administrator sistem.
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>PT Biro Klasifikasi Indonesia (Persero)</strong></p>
            <p>{{ config('app.name') }}</p>
            <div class="footer-links">
                <a href="{{ config('app.url') }}">Dashboard</a>
                <a href="{{ $alatUrl }}">Daftar Alat</a>
            </div>
            <p style="margin-top: 16px; font-size: 11px; color: #9ca3af;">
                &copy; {{ date('Y') }} PT BKI (Persero). All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
