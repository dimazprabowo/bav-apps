<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Cabang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Daftar Cabang</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama</th><th>Alamat</th><th>PIC</th><th>Alat</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cabangs as $i => $cabang)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $cabang->code }}</td>
            <td>{{ $cabang->name }}</td>
            <td>{{ $cabang->address ?? '-' }}</td>
            <td>{{ $cabang->pic_name ?? '-' }}</td>
            <td>{{ $cabang->alats_count }}</td>
            <td>{{ $cabang->status->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
