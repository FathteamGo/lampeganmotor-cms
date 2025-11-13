<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Exports\ProfitAndLossOneSheetExport;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Sale;
use App\Models\StnkRenewal;
use App\Models\WhatsAppNumber;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Http\Message\ResponseInterface;

class ProfitAndLossReport extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.profit_loss';
    protected static ?string $title = 'navigation.profit_loss';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.profit-and-loss-report';

    // === FILTER & STATE ===
    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    public float $totalSales = 0.0;
    public float $totalIncomes = 0.0;
    public float $totalExpenses = 0.0;
    public float $totalStnkIncome = 0.0;
    public float $totalStnkExpense = 0.0;

    // Modal WA manual
    public bool $waModalOpen = false;
    public array $wa = ['phone' => '', 'note' => ''];

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'owner';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'owner';
    }

    public static function getNavigationGroup(): ?string
    {
        return __(static::$navigationGroup);
    }

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel);
    }

    public function getTitle(): string
    {
        return __(static::$title);
    }

    public function mount(): void
    {
        $this->dateStart = now()->startOfMonth()->toDateString();
        $this->dateEnd   = now()->endOfMonth()->toDateString();
        $this->recalcTotals();
    }

    public function updated(string $prop): void
    {
        if (in_array($prop, ['dateStart', 'dateEnd'], true)) {
            $this->recalcTotals();
        }
    }

    /** Ekspor Excel */
    public function exportToExcel()
    {
        return Excel::download(
            new ProfitAndLossOneSheetExport($this->dateStart, $this->dateEnd, $this->search),
            "profit-loss_{$this->dateStart}_{$this->dateEnd}.xlsx"
        );
    }

    /** Hitung semua total (dengan filter tanggal konsisten) */
    public function recalcTotals(): void
    {
        $range = [$this->dateStart, $this->dateEnd];

        // SALES
        $this->totalSales = (float) Sale::query()
            ->where('status', '!=', 'CANCEL')
            ->whereBetween('sale_date', $range)
            ->sum('sale_price');

        // INCOME
        $this->totalIncomes = (float) Income::query()
            ->whereBetween('income_date', $range)
            ->sum('amount');

        // EXPENSES
        $this->totalExpenses = (float) Expense::query()
            ->whereBetween('expense_date', $range)
            ->sum('amount');

        // STNK
        $stnk = StnkRenewal::query()->whereBetween('tgl', $range);
        $this->totalStnkIncome = (float) (clone $stnk)->sum('margin_total');
        $this->totalStnkExpense = (float) (clone $stnk)->sum('payvendor');
    }

    /** Properti profit total */
    public function getProfitProperty(): float
    {
        return $this->totalSales
             + $this->totalIncomes
             + $this->totalStnkIncome
             - $this->totalExpenses
             - $this->totalStnkExpense;
    }

    /** Format rupiah */
    public function formatIdr(null|int|float $v): string
    {
        return 'Rp ' . number_format((float) ($v ?? 0), 0, ',', '.');
    }

    public function formatIdrSigned(float $v): string
    {
        $sign = $v < 0 ? '-' : '';
        return $sign . $this->formatIdr(abs($v));
    }

    /** Normalisasi nomor WA */
    protected function normalizePhone(string $raw): string
    {
        $p = preg_replace('/\D/', '', $raw);
        if (str_starts_with($p, '0'))  return '62' . substr($p, 1);
        if (str_starts_with($p, '62')) return $p;
        if (str_starts_with($p, '8'))  return '62' . $p;
        return $p;
    }

    /** Kirim laporan otomatis ke WA gateway */
    public function sendWhatsAppAuto(): void
    {
        $gateway = WhatsAppNumber::query()
            ->where('is_report_gateway', true)
            ->where('is_active', true)
            ->first();

        if (!$gateway) {
            Notification::make()
                ->title('Nomor WhatsApp Gateway tidak ditemukan!')
                ->body('Aktifkan nomor gateway di menu WhatsApp Table.')
                ->danger()
                ->send();
            return;
        }

        $phone = $this->normalizePhone($gateway->number);

        try {
            $res = app(WhatsAppService::class)->sendText($phone, $this->buildWaMessage());
            $ok  = $this->waSucceeded($res);
        } catch (\Throwable $e) {
            logger()->error('WA send error', ['e' => $e]);
            $ok = false;
        }

        $ok
            ? Notification::make()->title('Laporan terkirim via WhatsApp')->success()->send()
            : Notification::make()->title('Gagal mengirim laporan via WhatsApp')->danger()->send();
    }

    /** Validasi respon WA */
    protected function waSucceeded(mixed $res): bool
    {
        if (is_bool($res)) return $res;
        if (is_object($res) && method_exists($res, 'successful')) return $res->successful();
        if (is_object($res) && method_exists($res, 'status')) return ((int)$res->status() >= 200 && (int)$res->status() < 300);
        if ($res instanceof ResponseInterface) return ((int)$res->getStatusCode() >= 200 && (int)$res->getStatusCode() < 300);

        if (is_array($res)) {
            $status = strtolower((string) ($res['status'] ?? ''));
            return ($res['success'] ?? false)
                || in_array($status, ['ok', 'success', 'sent'], true)
                || isset($res['message_id'])
                || isset($res['messages'][0]['id']);
        }

        if (is_object($res)) {
            if (property_exists($res, 'success')) return (bool)$res->success;
            if (property_exists($res, 'status')) {
                $status = strtolower((string)$res->status);
                return in_array($status, ['ok', 'success', 'sent'], true);
            }
        }

        return false;
    }

    /** Template pesan WA */
    protected function buildWaMessage(?string $extraNote = null): string
    {
        $profit = $this->profit;

        $lines = [
            '📊 *Laporan Profit & Loss*',
            "Periode: {$this->dateStart} s/d {$this->dateEnd}",
            '--------------------------------',
            'SALES        : ' . $this->formatIdr($this->totalSales),
            'INCOME       : ' . $this->formatIdr($this->totalIncomes),
            'STNK INCOME  : ' . $this->formatIdr($this->totalStnkIncome),
            '--------------------------------',
            'EXPENSE      : ' . $this->formatIdr($this->totalExpenses),
            'STNK EXPENSE : ' . $this->formatIdr($this->totalStnkExpense),
            '--------------------------------',
            'TOTAL        : ' . $this->formatIdrSigned($profit) . ($profit >= 0 ? ' (Profit)' : ' (Loss)'),
        ];

        if ($extraNote && trim($extraNote) !== '') {
            $lines[] = '';
            $lines[] = 'Catatan: ' . trim($extraNote);
        }

        return implode("\n", $lines);
    }

    #[On('pl-dates-updated')]
    public function onDatesUpdated(string $start, string $end): void
    {
        $this->dateStart = $start;
        $this->dateEnd   = $end;
        $this->recalcTotals();
    }
}
