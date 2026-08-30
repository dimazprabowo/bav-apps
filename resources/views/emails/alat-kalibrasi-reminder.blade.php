<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'expired' ? 'Peringatan Kalibrasi Alat Kadaluarsa' : 'Peringatan Kalibrasi Alat Akan Jatuh Tempo' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            padding: 20px;
            line-height: 1.6;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        {{-- Header: gradient sesuai type (red=expired, amber=pending) --}}
        .header {
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header-expired {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }
        .header-pending {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }

        .logo {
            width: 80px;
            height: 80px;
            background-color: #ffffff;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 10px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 400;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .message {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        {{-- Warning box: sesuai type --}}
        .warning-box {
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .warning-box-expired {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
        }
        .warning-box-pending {
            background: linear-gradient(135deg, #fffbeb 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
        }
        .warning-box p {
            font-size: 15px;
            margin: 0;
            font-weight: 500;
        }
        .warning-box-expired p { color: #991b1b; }
        .warning-box-pending p { color: #92400e; }
        .warning-box strong { font-weight: 700; }

        {{-- Alat info card --}}
        .alat-info {
            background-color: #f9fafb;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }
        .alat-info h3 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .alat-info p {
            font-size: 16px;
            color: #1f2937;
            font-weight: 600;
            margin: 0;
        }

        {{-- Detail table --}}
        .detail-section {
            margin: 30px 0;
        }
        .detail-section h2 {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 16px;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .detail-table td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }
        .detail-table tr:last-child td {
            border-bottom: none;
        }
        .detail-table td:first-child {
            color: #6b7280;
            font-weight: 500;
            width: 45%;
        }
        .detail-table td:last-child {
            color: #1f2937;
            font-weight: 600;
        }
        .expiry-date-critical {
            color: #dc2626 !important;
            font-weight: 700 !important;
        }

        {{-- Action button --}}
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .action-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        {{-- Tips section --}}
        .tips-section {
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .tips-section h3 {
            font-size: 15px;
            color: #0369a1;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .tips-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .tips-section li {
            font-size: 14px;
            color: #0c4a6e;
            padding: 6px 0;
            padding-left: 24px;
            position: relative;
        }
        .tips-section li:before {
            content: "\2713";
            position: absolute;
            left: 0;
            color: #0ea5e9;
            font-weight: bold;
            font-size: 14px;
        }

        {{-- Info box --}}
        .info-box {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 16px 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .info-box p {
            font-size: 14px;
            color: #065f46;
            margin: 0;
        }
        .info-box strong { font-weight: 600; }

        {{-- Footer --}}
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .footer-links {
            margin-top: 15px;
        }
        .footer-links a {
            color: #2563eb;
            text-decoration: none;
            font-size: 13px;
            margin: 0 10px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }

        {{-- Responsive --}}
        @media only screen and (max-width: 600px) {
            body { padding: 12px 8px; }
            .header { padding: 30px 20px; }
            .header h1 { font-size: 20px; }
            .content { padding: 25px 20px; }
            .action-button {
                padding: 14px 32px;
                font-size: 15px;
                display: block;
            }
            .detail-table td { padding: 10px 12px; font-size: 13px; }
            .footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="header header-{{ $type === 'expired' ? 'expired' : 'pending' }}">
            <div class="logo">
                <img src="{{ email_logo_url() }}" alt="BKI Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h1>
                @if($type === 'expired')
                    Peringatan Kalibrasi Alat Kadaluarsa
                @else
                    Peringatan Kalibrasi Alat Akan Jatuh Tempo
                @endif
            </h1>
            <p>{{ config('app.name') }}</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <div class="greeting">
                Halo, {{ $userName }}
            </div>

            <div class="message">
                @if($type === 'expired')
                    Kami ingin menginformasikan bahwa kalibrasi alat berikut telah <strong>melewati tanggal kalibrasi berikutnya</strong> dan perlu segera dikalibrasi ulang untuk memastikan keakuratan pengukuran dan kepatuhan operasional.
                @else
                    Kami ingin menginformasikan bahwa kalibrasi alat berikut akan <strong>jatuh tempo dalam 30 hari ke depan</strong>. Mohon segera jadwalkan kalibrasi ulang sebelum masa berlaku habis.
                @endif
            </div>

            {{-- Warning box --}}
            <div class="warning-box warning-box-{{ $type === 'expired' ? 'expired' : 'pending' }}">
                <p>
                    @if($type === 'expired')
                        <strong>Perhatian!</strong> Alat ini sudah melewati tanggal kalibrasi berikutnya dan tidak boleh digunakan untuk operasional sampai dikalibrasi ulang.
                    @else
                        <strong>Pengingat!</strong> Alat ini akan jatuh tempo kalibrasi dalam ≤30 hari. Segera jadwalkan kalibrasi untuk menghindari keterlambatan.
                    @endif
                </p>
            </div>

            {{-- Alat info card --}}
            <div class="alat-info">
                <h3>Alat</h3>
                <p>{{ $alat->code }} &middot; {{ $alat->name }}</p>
            </div>

            {{-- Detail section --}}
            <div class="detail-section">
                <h2>Detail Alat</h2>
                <table class="detail-table">
                    <tr>
                        <td>Kode Alat</td>
                        <td>{{ $alat->code }}</td>
                    </tr>
                    <tr>
                        <td>Nama Alat</td>
                        <td>{{ $alat->name }}</td>
                    </tr>
                    <tr>
                        <td>Merk / Type</td>
                        <td>{{ $alat->merk_type ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Serial Number</td>
                        <td>{{ $alat->serial_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Cabang</td>
                        <td>{{ $cabang }}</td>
                    </tr>
                    <tr>
                        <td>Lokasi</td>
                        <td>{{ $alat->lokasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Kalibrasi Berikutnya</td>
                        <td class="{{ $type === 'expired' ? 'expiry-date-critical' : '' }}">{{ $tanggalExpired?->format('d M Y') ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- CTA button --}}
            <div class="button-container">
                <a href="{{ $alatUrl }}" class="action-button">Lihat Detail Alat</a>
            </div>

            {{-- Tips section --}}
            <div class="tips-section">
                <h3>Langkah yang Perlu Dilakukan:</h3>
                <ul>
                    <li>Periksa jadwal kalibrasi alat di sistem</li>
                    <li>Hubungi laboratorium kalibrasi terdekat</li>
                    <li>Ajukan permohonan kalibrasi melalui prosedur internal</li>
                    <li>Update data kalibrasi di sistem setelah selesai</li>
                    <li>Hubungi tim pengelola alat jika memerlukan bantuan</li>
                </ul>
            </div>

            {{-- Info box --}}
            <div class="info-box">
                <p><strong>Butuh bantuan?</strong> Tim pengelola alat siap membantu Anda dalam proses kalibrasi. Hubungi bagian terkait di cabang Anda.</p>
            </div>

            <div class="message" style="margin-top: 30px; padding-top: 25px; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 13px; color: #6b7280;">
                    Email ini dikirim secara otomatis oleh sistem.
                    Jika Anda merasa tidak seharusnya menerima email ini, silakan hubungi administrator.
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>PT Biro Klasifikasi Indonesia (Persero)</strong></p>
            <p>{{ config('app.name') }}</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Email ini dikirim secara otomatis, mohon tidak membalas email ini.
            </p>
            <div class="footer-links">
                <a href="{{ config('app.url') }}">Dashboard</a>
                <a href="{{ $alatUrl }}">Alat</a>
            </div>
            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                &copy; {{ date('Y') }} PT BKI (Persero). All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
