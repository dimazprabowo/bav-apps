<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Alat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Daftar Alat</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama</th><th>Cabang</th><th>Kondisi</th><th>Kalibrasi</th><th>Kepemilikan</th><th>Review</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alats as $i => $alat)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $alat->code }}</td>
            <td>{{ $alat->name }}</td>
            <td>{{ $alat->cabang?->name ?? '-' }}</td>
            <td>{{ $alat->kondisi->label() }}</td>
            <td>{{ $alat->status_kalibrasi_derived->label() }}</td>
            <td>{{ $alat->status_kepemilikan->label() }}</td>
            <td>{{ $alat->review_status->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
