<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Klaster</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Daftar Klaster</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Jumlah Vendor</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($klasters as $i => $klaster)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $klaster->code }}</td>
            <td>{{ $klaster->name }}</td>
            <td>{{ $klaster->description ?? '-' }}</td>
            <td>{{ $klaster->vendors_count }}</td>
            <td>{{ $klaster->status->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
