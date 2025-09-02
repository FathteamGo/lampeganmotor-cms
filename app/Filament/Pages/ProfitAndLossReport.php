<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Sale;
use Filament\Pages\Page;
use UnitEnum;

class ProfitAndLossReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?string $navigationLabel = 'Profit & Loss';
    protected static ?string $title = 'Laporan & Audit Profit & Loss';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.profit-and-loss-report';

    // Filter tanggal (global)
    public ?string $dateStart = null;
    public ?string $dateEnd   = null;

    // Ringkasan
    public float $totalSales    = 0.0;
    public float $totalIncomes  = 0.0;
    public float $totalExpenses = 0.0;

    public function mount(): void
    {
        $this->dateStart = now()->startOfMonth()->toDateString();
        $this->dateEnd   = now()->endOfMonth()->toDateString();
        $this->recalcTotals();
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['dateStart','dateEnd'], true)) {
            $this->recalcTotals();
        }
    }

    public function recalcTotals(): void
    {
        $s = $this->dateStart;
        $e = $this->dateEnd;

        $this->totalSales = (float) Sale::query()
            ->when($s, fn ($q) => $q->whereDate('sale_date', '>=', $s))
            ->when($e, fn ($q) => $q->whereDate('sale_date', '<=', $e))
            ->sum('sale_price');

        $this->totalIncomes = (float) Income::query()
            ->when($s, fn ($q) => $q->whereDate('income_date', '>=', $s))
            ->when($e, fn ($q) => $q->whereDate('income_date', '<=', $e))
            ->sum('amount');

        $this->totalExpenses = (float) Expense::query()
            ->when($s, fn ($q) => $q->whereDate('expense_date', '>=', $s))
            ->when($e, fn ($q) => $q->whereDate('expense_date', '<=', $e))
            ->sum('amount');
    }

    public function getProfitProperty(): float
    {
        return $this->totalSales + $this->totalIncomes - $this->totalExpenses;
    }

    public function formatIdr(null|int|float $v): string
    {
        return 'Rp '.number_format((float)($v ?? 0), 0, ',', '.');
    }
}
