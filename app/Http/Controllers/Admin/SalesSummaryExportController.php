<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesSummaryExport;

class SalesSummaryExportController extends Controller
{
    public function export(Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        return Excel::download(new SalesSummaryExport($month, $year), 'sales_summary.xlsx');
    }
}
