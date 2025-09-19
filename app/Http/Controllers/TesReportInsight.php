<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class TesReportInsight extends Controller
{
     public function index(ReportService $reportService)
    {
        $report = $reportService->generateWeeklyReport();

        return response()->json($report);
    }
}
