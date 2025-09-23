<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WeeklyReport;
use App\Models\UserModalDismiss;
use Illuminate\Support\Facades\Auth;

class AdminModal extends Component
{
    public $show = false;
    public $report;

    public function mount()
    {
        $this->loadReport();
    }

    public function loadReport()
    {
        $report = WeeklyReport::where('read', 0)->latest()->first();

        if ($report && !UserModalDismiss::where('user_id', Auth::id())
                ->where('modal_key', 'weekly_report_'.$report->id)
                ->exists()) 
        {
            $this->report = $report;
            $this->show = true;
        }
    }

    public function markAsRead()
    {
        if ($this->report) {
            $this->report->update(['read' => 1]);
        }
        $this->show = false;
    }

    public function janganTampilLagi()
    {
        if ($this->report) {
            UserModalDismiss::create([
                'user_id' => Auth::id(),
                'modal_key' => 'weekly_report_'.$this->report->id,
            ]);
        }
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.admin-modal');
    }
}
