<?php
namespace App\Http\Controllers;

use App\Exports\AssetReportExport;
use App\Models\OtherAsset;
use App\Models\Purchase;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AssetReportController extends Controller
{
    public function exportExcel(Request $request)
    {
        // Ambil filter tanggal dari request (opsional, jika ingin dinamis dari form)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Ambil data untuk Harta Tidak Bergerak
        $hartaTidakBergerak = OtherAsset::whereBetween('acquisition_date', [$startDate, $endDate])
            ->get(['name', 'description', 'acquisition_date as year', 'description', 'value as nominal']);

        // Ambil data untuk Tunggakan (asumsi berdasarkan notes mengandung 'tunggakan')
        $tunggakan = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('notes', 'like', '%tunggakan%')
            ->with('vehicle')
            ->get(['id', 'vehicle.displayName as name', 'category' => 'Tunggakan', 'purchase_date as year', 'notes as description', 'total_price as nominal']);

        // Ambil data untuk Stok Unit Tidak Bergerak
        $stokUnitTidakBergerak = Vehicle::where('status', 'available')
            ->get(['id', 'displayName as name', 'category' => 'Stok Unit Tidak Bergerak', 'purchase_date as year', 'vin as description', 'purchase_price as nominal']);

        // Gabungkan semua data
        $data = $hartaTidakBergerak->merge($tunggakan)->merge($stokUnitTidakBergerak);

        // Ekspor ke Excel
        return Excel::download(new AssetReportExport($data), 'asset_report_' . now()->format('Ymd_His') . '.xlsx');
    }
}
