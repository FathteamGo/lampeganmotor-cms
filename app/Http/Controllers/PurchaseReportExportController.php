<?php
namespace App\Http\Controllers;

use App\Filament\Exports\PurchaseReportExporter;
    use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PurchaseReportExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $from  = $request->query('from');
        $until = $request->query('until');

        return Excel::download(
            new PurchaseReportExporter($from, $until),
            'purchase-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
