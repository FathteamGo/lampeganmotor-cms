<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\WeeklyReport;

class WeeklyReportWidget extends Widget
{
    public string $view = 'filament.widgets.weekly-report-widget'; // non-static sekarang

    public $report;
    public $showModal = false;
    public $pollCount = 0;

    public function mount(): void
    {
        $this->checkWeeklyReport();
    }

    public function checkWeeklyReport()
    {
        $this->pollCount++;
        $this->report = WeeklyReport::where('read', false)->latest()->first();
        $this->showModal = $this->report ? true : false;

        $this->dispatchBrowserEvent('weekly-report-debug', [
            'report_exists' => $this->report ? true : false,
            'showModal' => $this->showModal,
        ]);
    }

    public function markAsRead()
    {
        if ($this->report) {
            $this->report->update(['read' => true]);
            $this->showModal = false;
        }
    }
}
