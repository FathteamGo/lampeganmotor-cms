<?php

namespace App\Filament\Widgets;

use App\Models\WeeklyReport;
use Filament\Widgets\Widget;

class WeeklyReportWidget extends Widget
{
   
    protected string $view = 'filament.widgets.weekly-report-widget';

    public function getUnreadReportsCount(): int
    {
        return WeeklyReport::where('read', 0)->count();
    }
}
