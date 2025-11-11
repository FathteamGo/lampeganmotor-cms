<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $sale->id }}</title>
    <style>
        /* ===== BASE STYLES ===== */
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11px; 
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        /* ===== HEADER SECTION ===== */
        .header { 
            display: table;
            width: 100%;
            border-bottom: 2px solid #000; 
            padding-bottom: 12px; 
            margin-bottom: 15px; 
        }
        
        .header-left {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            padding-right: 15px;
        }

        .header-left img { 
            width: 85px;
            height: 85px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            padding-left: 5px;
        }
        
        .header-right h2 { 
            margin: 0 0 4px 0; 
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .header-right p { 
            margin: 2px 0; 
            font-size: 10px;
            line-height: 1.4;
        }

        /* ===== INVOICE TITLE ===== */
        .invoice-title {
            text-align: center;
            margin: 10px 0;
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td { 
            padding: 3px 6px; 
            vertical-align: top;
            font-size: 11px;
        }

        .info-table td:first-child {
            width: 50%;
        }

        /* ===== DETAIL TABLE ===== */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .detail-table th,
        .detail-table td {
            border: 1px solid #aaa;
            padding: 5px 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .detail-table th { 
            background-color: #f0f0f0;
            font-weight: bold;
            width: 35%;
        }

        .price-row {
            background-color: #fffacd;
        }

        .price-row th,
        .price-row td {
            font-weight: bold;
            font-size: 12px;
        }

        /* ===== SIGNATURE SECTION ===== */
        .signature {
            width: 100%;
            margin-top: 30px;
        }
        
        .signature table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }

        .signature p {
            margin-top: 50px;
            border-top: 1px solid #333;
            display: inline-block;
            padding-top: 3px;
            min-width: 120px;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
        }

        /* ===== UTILITY CLASSES ===== */
        .text-bold {
            font-weight: bold;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-danger {
            color: #ff0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/logo/lampeganmtrbdg.jpg') }}" alt="Logo Lampegan Motor">
        </div>
        <div class="header-right">
            <h2>LAMPEGAN MOTOR BDG</h2>
            <p>Jl. Raya Majalaya-Paseh No. 247, Kab. Bandung</p>
            <p>Telp: 0813-9451-0605 | Website: lampeganmotorbdg.com</p>
        </div>
    </div>

    <!-- Invoice Title -->
    <div class="invoice-title">INVOICE PEMBELIAN CASH</div>

    <!-- Customer & Invoice Info -->
    <table class="info-table">
        <tr>
            <td>
                <strong>No. Invoice:</strong> 
                INV-{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}
            </td>
            <td>
                <strong>Tanggal:</strong> 
                {{ $sale->sale_date->format('d M Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Nama Customer:</strong> 
                {{ $sale->customer->name ?? '-' }}
            </td>
            <td>
                <strong>No. Telepon:</strong> 
                {{ $sale->customer->phone ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Alamat:</strong> 
                {{ $sale->customer->address ?? '-' }}
            </td>
            <td>
                <strong>Sales:</strong> 
                {{ $sale->user->name ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- Vehicle Details -->
    <table class="detail-table">
        <tr>
            <th>Jenis Motor</th>
            <td>
                {{ $sale->vehicle->vehicleModel->brand->name ?? '-' }} 
                {{ $sale->vehicle->vehicleModel->name ?? '-' }}
            </td>
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
        <tr class="price-row">
            <th>Harga Jual</th>
            <td>Rp {{ number_format($sale->sale_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ strtoupper($sale->payment_method ?? '-') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if(strtolower($sale->status ?? '') === 'cancel')
                    <span class="text-danger">DIBATALKAN</span>
                @else
                    {{ strtoupper($sale->status ?? '-') }}
                @endif
            </td>
        </tr>
    </table>

    <!-- Signature Section -->
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

    <!-- Footer -->
    <div class="footer">
        <p>
            Terima kasih telah mempercayakan pembelian kendaraan Anda di 
            <strong>LAMPEGAN MOTOR BDG</strong>.
        </p>
    </div>
</body>
</html>