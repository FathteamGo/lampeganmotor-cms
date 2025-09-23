<?php
namespace App\Http\Controllers;

use App\Models\UserModalDismiss;
use App\Models\WeeklyReport;
use Illuminate\Support\Facades\Auth;

class WeeklyReportController extends Controller
{
    public function dismiss(WeeklyReport $report)
    {
        UserModalDismiss::updateOrCreate([
            'user_id' => Auth::id(),
            'modal_key' => 'weekly_report_' . $report->id,
        ]);

        return redirect()->back();
    }
}
