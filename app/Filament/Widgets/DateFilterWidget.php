<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;

class DateFilterWidget extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -2; 

    public $startDate;
    public $endDate;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedStartDate(): void
    {
        $this->dispatch('filterChanged', startDate: $this->startDate, endDate: $this->endDate);
    }

    public function updatedEndDate(): void
    {
        $this->dispatch('filterChanged', startDate: $this->startDate, endDate: $this->endDate);
    }

    public function resetFilter(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->dispatch('filterChanged', startDate: $this->startDate, endDate: $this->endDate);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.date-filter-widget');
    }
}
