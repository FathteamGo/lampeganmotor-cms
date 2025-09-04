<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Exports\ProfitAndLossExport;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Sale;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Http\Message\ResponseInterface;
use UnitEnum;

class ProfitAndLossReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.profit_loss';
    protected static ?string $title = 'navigation.profit_loss'; 



    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.profit-and-loss-report';


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

     public static function shouldRegisterNavigation(): bool
{
    $user = Auth::user();

    return $user && $user->role === 'owner';
}


 public static function canAccess(): bool
    {
    $user = Auth::user();

    return $user && $user->role === 'owner';
    }

    // Filter tanggal (global)
    // Header filter
    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    // Summary totals
    public float $totalSales = 0.0;
    public float $totalIncomes = 0.0;
    public float $totalExpenses = 0.0;

    // (opsional) modal manual WA
    public bool $waModalOpen = false;
    public array $wa = ['phone' => '', 'note' => ''];

    /** Nomor default auto-send */
    protected string $waAutoNumber = '081394510605';

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

    /** ===== Actions dipanggil dari Blade ===== */
    public function exportToExcel()
    {
        return Excel::download(
            new \App\Exports\ProfitAndLossOneSheetExport($this->dateStart, $this->dateEnd, $this->search),
            "profit-loss_{$this->dateStart}_{$this->dateEnd}.xlsx"
        );
    }



    /** Normalisasi 08xxxx -> 62xxxx */
    protected function normalizePhone(string $raw): string
    {
        $p = preg_replace('/\D/', '', $raw);
        if (str_starts_with($p, '0'))  return '62' . substr($p, 1);
        if (str_starts_with($p, '62')) return $p;
        if (str_starts_with($p, '8'))  return '62' . $p;
        return $p;
    }

    /** Tentukan apakah respons WA dianggap sukses */
    protected function waSucceeded(mixed $res): bool
    {
        if (is_bool($res)) return $res;

        if (is_object($res) && method_exists($res, 'successful')) {
            return (bool) $res->successful();
        }
        if (is_object($res) && method_exists($res, 'status')) {
            $code = (int) $res->status();
            return $code >= 200 && $code < 300;
        }

        if ($res instanceof ResponseInterface) {
            $code = (int) $res->getStatusCode();
            return $code >= 200 && $code < 300;
        }

        if (is_array($res)) {
            $status = strtolower((string) ($res['status'] ?? ''));
            return ($res['success'] ?? null) === true
                || in_array($status, ['ok', 'success', 'sent'], true)
                || isset($res['message_id'])
                || isset($res['messages'][0]['id']);
        }

        if (is_object($res)) {
            if (property_exists($res, 'success')) return (bool) $res->success;
            if (property_exists($res, 'status')) {
                $status = strtolower((string) $res->status);
                return in_array($status, ['ok', 'success', 'sent'], true);
            }
            if (property_exists($res, 'message_id')) return true;
        }

        return false;
    }

    /** AUTO SEND TANPA MODAL */
    public function sendWhatsAppAuto(): void
    {
        $phone = $this->normalizePhone($this->waAutoNumber);

        try {
            $res = app(WhatsAppService::class)->sendText($phone, $this->buildWaMessage());
            $ok  = $this->waSucceeded($res);
        } catch (\Throwable $e) {
            $ok = false;
            logger()->error('WA send error', ['e' => $e]);
        }
    }

    /** Buka modal manual (jika dipakai) */
    public function openWaModal(): void
    {
        $this->dispatch('open-modal', id: 'send-wa');
    }

    /** Kirim via modal manual */
    public function sendWhatsApp(): void
    {
        $this->validate([
            'wa.phone' => ['required', 'regex:/^[0-9]+$/'],
            'wa.note'  => ['nullable', 'string', 'max:500'],
        ]);

        $phone = preg_replace('/[^0-9]/', '', $this->wa['phone']);

        try {
            $res = app(WhatsAppService::class)->sendText($phone, $this->buildWaMessage($this->wa['note'] ?? null));
            $ok  = $this->waSucceeded($res);
        } catch (\Throwable $e) {
            $ok = false;
            logger()->error('WA send error', ['e' => $e]);
        }

        $ok
            ? Notification::make()->title('WhatsApp terkirim')->success()->send()
            : Notification::make()->title('Gagal mengirim WhatsApp')->body('Cek kredensial & nomor.')->danger()->send();

        $this->waModalOpen = false;
        $this->wa = ['phone' => '', 'note' => ''];
    }

    /** ===== Perhitungan ===== */
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
        return 'Rp ' . number_format((float) ($v ?? 0), 0, ',', '.');
    }

    /** Tambahan: formatter polos & bertanda untuk kebutuhan WA */
    public function formatIdrPlain(null|int|float $v): string
    {
        return number_format((float) ($v ?? 0), 0, ',', '.');
    }

    public function formatIdrSigned(float $v): string
    {
        $sign = $v < 0 ? '-' : '';
        return $sign . 'Rp ' . $this->formatIdrPlain(abs($v));
    }

    protected function buildWaMessage(?string $extraNote = null): string
    {
        $profit = $this->profit;

        $lines = [
            'Laporan Profit & Loss',
            "Periode: {$this->dateStart} s/d {$this->dateEnd}",
            '--------------------------------',
            'SALES   : ' . $this->formatIdr($this->totalSales),
            'INCOME  : ' . $this->formatIdr($this->totalIncomes),
            // EXPENSE TANPA MINUS
            'EXPENSE : ' . $this->formatIdr($this->totalExpenses),
            '--------------------------------',
            // TOTAL: minus hanya jika loss
            'TOTAL   : ' . $this->formatIdrSigned($profit) . ($profit >= 0 ? ' (Profit)' : ' (Loss)'),
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
