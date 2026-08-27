<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LogBook Peminjaman</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>LogBook Peminjaman Alat</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>Alat</th><th>Peminjam</th><th>Cabang</th><th>Tgl Pinjam</th><th>Rencana Kembali</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $i => $log)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $log->alat?->name ?? '-' }}</td>
            <td>{{ $log->peminjam?->name ?? '-' }}</td>
            <td>{{ $log->cabang?->name ?? '-' }}</td>
            <td>{{ $log->tanggal_pinjam->format('d/m/Y') }}</td>
            <td>{{ $log->tanggal_kembali_rencana->format('d/m/Y') }}</td>
            <td>{{ $log->status->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
