<?php

namespace App\Filament\Resources\CashTempoTrackings\Pages;

use App\Filament\Resources\CashTempoTrackings\CashTempoTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListCashTempoTrackings extends ListRecords
{
    protected static string $resource = CashTempoTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada Create Action karena data dari Sales
        ];
    }

    /**
     * Custom heading dengan total uang mengendap
     */
    public function getHeading(): string
    {
        $total = DB::table('sales')
            ->where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0)
            ->sum('remaining_payment');

        $formatted = 'Rp ' . number_format($total, 0, ',', '.');

        return "Cash Tempo Tracking - Total Mengendap: {$formatted}";
    }

    /**
     * Subheading dengan info jatuh tempo
     */
    public function getSubheading(): ?string
    {
        $jatuhTempo = DB::table('sales')
            ->where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now())
            ->count();

        $akan7Hari = DB::table('sales')
            ->where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->count();

        $info = [];
        
        if ($jatuhTempo > 0) {
            $info[] = "🔴 {$jatuhTempo} sudah jatuh tempo";
        }
        
        if ($akan7Hari > 0) {
            $info[] = "🟡 {$akan7Hari} akan jatuh tempo 7 hari";
        }

        return !empty($info) ? implode(' | ', $info) : 'Semua cash tempo masih aman';
    }
}