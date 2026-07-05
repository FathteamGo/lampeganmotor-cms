<?php
namespace App\Services;

use App\Models\WeeklyReport;
use Carbon\Carbon;

class ReportNotificationService
{
    /**
     * Build the full report message to be sent via WhatsApp.
     * Includes period, visitor count, sales, income, expense, stock, STNK renewal,
     * top motors, insight, and comparison with previous week.
     */
    public function buildReportMessage(WeeklyReport $report): string
    {
        $startDate = Carbon::parse($report->start_date)->format('d M Y');
        $endDate   = Carbon::parse($report->end_date)->format('d M Y');

        $topMotors = collect($report->top_motors)
            ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
            ->implode("\n");

        if (empty($topMotors)) {
            $topMotors = "• Belum ada penjualan minggu ini";
        }

        $bosName = env('BOS_NAME', 'Bos');
        $showroomName = env('SHOWROOM_NAME', 'Lampegan Motor');

        $netProfit = $report->total_income - $report->expense_total;

        $message = "👑 *LAPORAN MINGGUAN SHOWROOM* 👑\n" .
            "🌸 *Assalamu'alaikum, {$bosName}!* Saya Hana AI, asisten digital Anda.\n\n" .
            "Berikut adalah rangkuman performa *{$showroomName}* Periode *{$startDate} - {$endDate}*:\n\n" .
            "1. 👥 Pengunjung: {$report->visitors} orang\n" .
            "2. 🏍️ Penjualan: {$report->sales_count} unit (Rp " . number_format($report->sales_total, 0, ',', '.') . ")\n" .
            "3. 💰 Total Pendapatan: Rp " . number_format($report->total_income, 0, ',', '.') . "\n" .
            "4. 📉 Total Pengeluaran: Rp " . number_format($report->expense_total, 0, ',', '.') . "\n" .
            "5. 💵 Pendapatan Bersih: Rp " . number_format($netProfit, 0, ',', '.') . "\n" .
            "6. 📦 Sisa Stok Showroom: {$report->stock} unit\n" .
            "7. 📝 Perpanjangan STNK: {$report->stnk_renewal} transaksi\n\n" .
            "🏆 *Motor Terlaris Minggu Ini*:\n{$topMotors}\n\n" .
            "💡 *Insight & Evaluasi Hana AI*:\n{$report->insight}\n\n" .
            ($report->comparison ?? '') . "\n\n" .
            "--- \n" .
            "⚠️ Disclaimer: Laporan ini dibuat otomatis oleh Hana AI. Semoga berkah dan melancarkan usaha {$bosName} ya! 🌸";
        return $message;
    }

    /**
     * Build a concise weekly insight message.
     */
    public function buildInsightMessage(WeeklyReport $report): string
    {
        $startDate = Carbon::parse($report->start_date)->format('d M Y');
        $endDate   = Carbon::parse($report->end_date)->format('d M Y');

        $topMotors = collect($report->top_motors)
            ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
            ->implode("\n");

        if (empty($topMotors)) {
            $topMotors = "• Tidak ada penjualan minggu ini";
        }

        // Net income/profit calculation
        $netProfit = $report->total_income - $report->expense_total;
        $formattedProfit = number_format($netProfit, 0, ',', '.');
        $formattedSales = number_format($report->sales_total, 0, ',', '.');

        $formattedIncome = number_format($report->total_income, 0, ',', '.');
        $formattedExpense = number_format($report->expense_total, 0, ',', '.');

        $bosName = env('BOS_NAME', 'Bos');
        $showroomName = env('SHOWROOM_NAME', 'Lampegan Motor');

        $message = "👑 *INSIGHT STRATEGIS MINGGUAN* 👑\n" .
            "🌸 _Hana AI - Asisten Digital Anda_\n\n" .
            "Assalamu'alaikum {$bosName}! Berikut adalah *analisis bisnis strategis* & insight penting untuk *{$showroomName}* periode *{$startDate} - {$endDate}*:\n\n" .
            "💡 *STRATEGIC BUSINESS INSIGHTS*:\n" .
            "{$report->insight}\n\n" .
            "📊 *KEY PERFORMANCE INDICATORS*:\n" .
            "• 👥 Total Pengunjung: {$report->visitors} orang\n" .
            "• 🏍️ Motor Terjual: {$report->sales_count} unit (Rp {$formattedSales})\n" .
            "• 💰 Total Pendapatan: Rp {$formattedIncome}\n" .
            "• 📉 Total Pengeluaran: Rp {$formattedExpense}\n" .
            "• 💵 Pendapatan Bersih: Rp {$formattedProfit}\n" .
            "• 📦 Stok Showroom saat ini: {$report->stock} unit\n\n" .
            "🏆 *UNIT PALING BANYAK DICARI*:\n" .
            "{$topMotors}\n\n" .
            "--- \n" .
            "Semoga berkah dan membantu {$bosName} dalam mengambil langkah taktis untuk mendongkrak penjualan minggu depan ya, {$bosName}! Semangat! 🚀";

        return $message;
    }

    /**
     * Build the 30-day strategic owner insight message.
     */
    public function build30DayInsightMessage(array $data, string $insight): string
    {
        $startDate = Carbon::parse($data['periode']['mulai'])->format('d M Y');
        $endDate   = Carbon::parse($data['periode']['selesai'])->format('d M Y');

        $topMotors = collect($data['top_motors'])
            ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
            ->implode("\n");

        if (empty($topMotors)) {
            $topMotors = "• Tidak ada penjualan dalam 30 hari terakhir";
        }

        $netProfit = $data['total_income'] - $data['keuangan']['pengeluaran'];
        $formattedProfit = number_format($netProfit, 0, ',', '.');
        $formattedSales = number_format($data['penjualan']['total'], 0, ',', '.');

        $formattedIncome = number_format($data['total_income'], 0, ',', '.');
        $formattedExpense = number_format($data['keuangan']['pengeluaran'], 0, ',', '.');

        $bosName = env('BOS_NAME', 'Bos');
        $showroomName = env('SHOWROOM_NAME', 'Lampegan Motor');

        $message = "👑 *INSIGHT STRATEGIS 30 HARI - OWNER* 👑\n" .
            "🌸 _Hana AI - Asisten Digital Anda_\n\n" .
            "Assalamu'alaikum {$bosName}! Berikut adalah *analisis bisnis & evaluasi taktis* untuk *{$showroomName}* 30 hari ke belakang (*{$startDate} - {$endDate}*):\n\n" .
            "💡 *STRATEGIC BUSINESS INSIGHTS*:\n" .
            "{$insight}\n\n" .
            "📊 *KEY PERFORMANCE INDICATORS (30 HARI)*:\n" .
            "• 👥 Total Pengunjung: {$data['pengunjung']} orang\n" .
            "• 🏍️ Motor Terjual: {$data['penjualan']['jumlah']} unit (Rp {$formattedSales})\n" .
            "• 💰 Total Pendapatan: Rp {$formattedIncome}\n" .
            "• 📉 Total Pengeluaran: Rp {$formattedExpense}\n" .
            "• 💵 Pendapatan Bersih: Rp {$formattedProfit}\n" .
            "• 📦 Stok Showroom saat ini: {$data['stok']} unit\n\n" .
            "🏆 *UNIT PALING LAKU (30 HARI)*:\n" .
            "{$topMotors}\n\n" .
            "--- \n" .
            "Semoga berkah dan membantu {$bosName} dalam merancang strategi agar showroom kita semakin ramai dan berkah ya, {$bosName}! Semangat! 🚀";

        return $message;
    }
}
