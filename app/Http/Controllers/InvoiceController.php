<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function cash(Sale $sale)
    {
        if ($sale->payment_method !== 'cash') {
            abort(403, 'Invoice ini hanya untuk transaksi cash.');
        }

        $pdf = Pdf::loadView('pdf.invoice_cash', ['sale' => $sale]);

        return $pdf->stream("invoice_cash_{$sale->id}.pdf");
    }
}
