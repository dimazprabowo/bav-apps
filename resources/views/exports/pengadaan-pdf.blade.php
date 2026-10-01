<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pengadaan Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
<h2>Daftar Pengadaan Aset</h2>
<table>
    <thead>
        <tr>
            <th>No</th><th>No. Pengadaan</th><th>Pemohon</th><th>Tipe Biaya</th><th>Project</th><th>No. WBS</th><th>Vendor</th><th>Cabang</th><th>Tanggal</th><th>Item</th><th>Total Biaya</th><th>Status Approval</th><th>Status Invoice</th><th>Status Pembayaran</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pengadaans as $i => $pengadaan)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $pengadaan->no_pengadaan }}</td>
            <td>{{ $pengadaan->nama_pemohon }}</td>
            <td>{{ $pengadaan->tipe_biaya->label() }}</td>
            <td>{{ $pengadaan->nama_project ?? '-' }}</td>
            <td>{{ $pengadaan->no_wbs ?? '-' }}</td>
            <td>{{ $pengadaan->vendor->name }}</td>
            <td>{{ $pengadaan->cabang->name ?? 'Pusat' }}</td>
            <td>{{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</td>
            <td>{{ $pengadaan->items_count }}</td>
            <td>Rp {{ number_format($pengadaan->total_biaya, 0, ',', '.') }}</td>
            <td>{{ $pengadaan->status_approval->label() }}</td>
            <td>{{ $pengadaan->status_invoice->label() }}</td>
            <td>{{ $pengadaan->status_pembayaran->label() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
