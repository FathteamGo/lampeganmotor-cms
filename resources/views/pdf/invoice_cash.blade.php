<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $sale->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 12px; }

        .info-table, .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .info-table td { padding: 3px 6px; vertical-align: top; }
        .detail-table th, .detail-table td {
            border: 1px solid #aaa;
            padding: 6px;
            text-align: left;
        }
        .detail-table th { background-color: #f0f0f0; }

        .footer {
            margin-top: 40px;
            width: 100%;
            text-align: center;
            font-size: 11px;
        }

        .signature {
            width: 100%;
            margin-top: 40px;
            text-align: center;
        }
        .signature td {
            width: 50%;
            vertical-align: top;
        }
        .signature p {
            margin-top: 60px;
            border-top: 1px solid #333;
            display: inline-block;
            padding-top: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAMPEGAN MOTOR BANDUNG</h2>
        <p>Jl Raya Majalaya-Paseh No 247, Kab.Bandung</p>
        <p>Telp. 6281394510605 | Website: lampeganmotorbdg.com</p>
    </div>

    <h3 style="text-align:center; text-decoration:underline;">INVOICE PEMBELIAN CASH</h3>

    <table class="info-table">
        <tr>
            <td><strong>No. Invoice:</strong> INV-{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td><strong>Tanggal:</strong> {{ $sale->sale_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><strong>Nama Customer:</strong> {{ $sale->customer->name ?? '-' }}</td>
            <td><strong>No. Telepon:</strong> {{ $sale->customer->phone ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Alamat:</strong> {{ $sale->customer->address ?? '-' }}</td>
            <td><strong>Sales:</strong> {{ $sale->user->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="detail-table">
        <tr>
            <th>Jenis Motor</th>
            <td>{{ $sale->vehicle->vehicleModel->brand->name ?? '-' }}</td>
        </tr>
       <tr>
            <th>Type</th>
            <td>{{ $sale->vehicle->type->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tahun</th>
            <td>{{ $sale->vehicle->year->year ?? '-' }}</td>
        </tr>
        <tr>
            <th>Warna</th>
            <td>{{ $sale->vehicle->color->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>No. Polisi</th>
            <td>{{ $sale->vehicle->license_plate ?? '-' }}</td>
        </tr>
        <tr>
            <th>Harga OTR</th>
            <td>Rp {{ number_format($sale->sale_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Pencairan</th>
            <td>Rp {{ number_format($sale->pencairan, 0, ',', '.') }}</td>
        </tr>   
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ strtoupper($sale->payment_method ?? '-') }}</td>
        </tr>
    </table>

    <div class="signature">
        <table>
            <tr>
                <td>
                    <p>Sales / Admin</p>
                </td>
                <td>
                    <p>Customer</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Terima kasih telah mempercayakan pembelian kendaraan Anda di <strong>LAMPEGAN MOTOR</strong>.</p>
    </div>
</body>
</html>
