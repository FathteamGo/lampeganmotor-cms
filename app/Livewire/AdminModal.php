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

    protected $listeners = ['showWeeklyReportModal' => 'showModal'];

    public function mount()
    {
        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.admin-modal');
    }

    protected function loadReport()
    {
        $dismissed = UserModalDismiss::where('user_id', Auth::id())
            ->where('modal_key', 'weekly_report_reminder')
            ->exists();

        if (!$dismissed) {
            $this->report = WeeklyReport::where('read', 0)->latest()->first();
            $this->show = $this->report ? true : false;
        }
    }

    public function showModal()
    {
        $this->loadReport();
        $this->show = $this->report ? true : false;
    }

    public function oke()
    {
        $this->show = false;
    }

    public function janganTampilLagi()
    {
        UserModalDismiss::create([
            'user_id' => Auth::id(),
            'modal_key' => 'weekly_report_reminder',
        ]);
        $this->show = false;
    }
}
