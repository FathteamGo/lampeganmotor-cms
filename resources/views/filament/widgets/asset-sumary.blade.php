<x-filament-widgets::widget>
    <x-filament::card>
        <table class="w-full text-sm border-collapse">
            <tbody>
                @php
                $hartaTidakBergerak = \App\Models\OtherAsset::sum('value');
                $stokUnitBergerak = \App\Models\Purchase::sum('total_price');
                $piutangDiluar = 0; // sesuaikan kalau ada tabel piutang
                $pencairanAdira = 108300000; // contoh hardcode dulu
                $avalist = 157000000; // contoh hardcode dulu
                $asetSaldo = 0; // sesuaikan
                $total = $hartaTidakBergerak + $stokUnitBergerak + $piutangDiluar + $pencairanAdira + $avalist + $asetSaldo;
                @endphp
                <tr>
                    <th class="py-2">Asset</th>
                    <td></td>
                    <th class="py-2 text-right">Nominal</th>
                </tr>
                <tr>
                    <td class="py-2">HARTA TIDAK BERGERAK</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($hartaTidakBergerak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2">STOCK UNIT BERGERAK</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($stokUnitBergerak, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2">PIUTANG DILUAR</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($piutangDiluar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2">PENCAIRAN ADIRA FINANCE</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($pencairanAdira, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2">AVALIST DAN STOK UNIT TIDAK BERGERAK</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($avalist, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2">ASET BERUPA SALDO / UANG</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($asetSaldo, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="font-bold border-t">
                    <td class="py-2">Total Asset</td>
                    <td>:</td>
                    <td class="py-2 text-right">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </x-filament::card>
</x-filament-widgets::widget>

