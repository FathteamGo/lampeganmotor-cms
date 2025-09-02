<?php
namespace App\Http\Controllers;

use App\Filament\Exports\SalesReportExporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $from  = $request->query('from');
        $until = $request->query('until');

        return Excel::download(
            new SalesReportExporter($from, $until),
            'sales-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
