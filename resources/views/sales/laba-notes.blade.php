<x-filament::section>
    <x-slot name="heading">
        Catatan Perhitungan Laba
    </x-slot>

    <div class="text-sm space-y-4">

        <p class="text-danger-500 font-medium">
            *Laba = 0 artinya minus (rugi) setelah dipotong semua komponen.*
        </p>

        <div class="space-y-2">
            <h3 class="font-semibold">Kredit</h3>
            <p>OTR - DP PO - DP Real - Harga Total Pembelian = Laba</p>

            <h3 class="font-semibold">Cash</h3>
            <p>OTR - Harga Total Pembelian = Laba</p>

            <h3 class="font-semibold">Cash Tempo</h3>
            <p>OTR - Harga Total Pembelian = Laba</p>
            <p>Harga Total Pembelian - DP = Sisa Pembayaran</p>
            <p class="text-xs italic text-gray-500">
                *Menu cash tempo digunakan untuk tracking uang mengendap.*
            </p>

            <h3 class="font-semibold">Dana Tunai</h3>
            <p>OTR - DP PO - Pembayaran ke Nasabah = Laba</p>
        </div>

        <div class="pt-3 border-t">
            <h3 class="font-semibold">Laba Bersih</h3>
            <p>Laba Penjualan - Pengeluaran (CMO Fee, Komisi, dll.)</p>
        </div>
    </div>

</x-filament::section>
