<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Vendor</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Daftar Vendor</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama</th><th>Kontak</th><th>Telepon</th><th>Email</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendors as $i => $vendor)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $vendor->code }}</td>
            <td>{{ $vendor->name }}</td>
            <td>{{ $vendor->contact_person ?? '-' }}</td>
            <td>{{ $vendor->phone ?? '-' }}</td>
            <td>{{ $vendor->email ?? '-' }}</td>
            <td>{{ $vendor->status->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
