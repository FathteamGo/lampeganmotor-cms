<?php

namespace Tests\Feature;

use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\EditSale;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Alur buyback lewat halaman Filament yang sebenarnya.
 *
 * Berbeda dengan VehicleResellCycleTest yang menguji aturan di level model,
 * test ini MENJALANKAN class halaman (CreatePurchase, CreateSale, EditSale)
 * lewat Livewire — jadi kode yang diuji adalah kode yang benar-benar dipakai
 * user, bukan simulasinya.
 *
 * Butuh MySQL/MariaDB. Jalankan dengan:
 *   vendor/bin/phpunit -c phpunit.uitest.xml
 */
class BuybackUiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrasi project memakai sintaks MySQL-only, jadi test ini tidak bisa
        // jalan di suite default yang memakai SQLite.
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh MySQL/MariaDB — jalankan: vendor/bin/phpunit -c phpunit.uitest.xml');
        }

        // Pengaman: RefreshDatabase menjalankan migrate:fresh. Jangan sampai
        // test ini pernah menyentuh database selain database khusus test.
        $database = DB::connection()->getDatabaseName();
        if (! str_contains($database, 'uitest')) {
            $this->fail(
                "Test dihentikan demi keamanan: database '{$database}' bukan database test. "
                . "Jalankan dengan: vendor/bin/phpunit -c phpunit.uitest.xml"
            );
        }

        $this->actingAs(User::factory()->create(['role' => 'owner']));
    }

    /** Data pembelian minimal yang valid untuk form Pembelian */
    private function purchaseFormData(array $overrides = []): array
    {
        $supplier = Supplier::firstOrCreate(['name' => 'Supplier A']);

        return array_merge([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'brand_name' => 'Honda',
            'vehicle_model_name' => 'Beat',
            'type_name' => 'Matic',
            'color_name' => 'Hitam',
            'year_name' => '2020',
            'vin' => 'VINBUYBACK001',
            'engine_number' => 'ENGBUYBACK001',
            'license_plate' => 'D 1234 AB',
            'purchase_price' => 9_000_000,
            'sale_price' => 11_000_000,
        ], $overrides);
    }

    private function createPurchaseViaUi(array $overrides = []): void
    {
        Livewire::test(CreatePurchase::class)
            ->fillForm($this->purchaseFormData($overrides))
            ->call('create')
            ->assertHasNoFormErrors();
    }

    // ===========================================================
    // 1. Pembelian awal dari supplier
    // ===========================================================

    public function test_pembelian_baru_lewat_ui_membuat_motor_available(): void
    {
        $this->createPurchaseViaUi();

        $vehicle = Vehicle::where('vin', 'VINBUYBACK001')->first();

        $this->assertNotNull($vehicle, 'Kendaraan tidak terbentuk dari form Pembelian.');
        $this->assertSame('available', $vehicle->status);
        $this->assertSame(1, Purchase::where('vehicle_id', $vehicle->id)->count());
        $this->assertTrue($vehicle->isSellable());
    }

    // ===========================================================
    // 2. Siklus penuh: beli → jual selesai → buyback → jual lagi
    // ===========================================================

    public function test_siklus_penuh_buyback_lewat_ui(): void
    {
        // --- Beli dari supplier
        $this->createPurchaseViaUi();
        $vehicle = Vehicle::where('vin', 'VINBUYBACK001')->first();
        $purchase1 = Purchase::where('vehicle_id', $vehicle->id)->first();

        // --- Jual ke customer lewat form Penjualan
        Livewire::test(CreateSale::class)
            ->fillForm([
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'customer_name' => 'Budi',
                'customer_phone' => '08123456789',
                'customer_nik' => '3204010101900001',
                'sale_date' => now()->subMonths(3)->toDateString(),
                'sale_price' => 11_000_000,
                'payment_method' => 'cash',
                'status' => 'proses',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sale1 = Sale::where('vehicle_id', $vehicle->id)->firstOrFail();
        $vehicle->refresh();

        $this->assertSame('sold', $vehicle->status, 'Motor harus jadi sold setelah dijual.');
        $this->assertSame($purchase1->id, $sale1->purchase_id, 'Sale harus terikat ke pembelian siklus 1.');

        // --- Selesaikan penjualan
        Livewire::test(EditSale::class, ['record' => $sale1->getKey()])
            ->fillForm(['status' => 'selesai'])
            ->call('save')
            ->assertHasNoFormErrors();

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status, 'Motor selesai dikirim tetap sold.');

        // --- BUYBACK: customer menjual kembali ke showroom
        $this->createPurchaseViaUi(['purchase_price' => 10_000_000]);

        $vehicle->refresh();
        $this->assertSame(
            'available',
            $vehicle->status,
            'Buyback harus mengembalikan motor jadi stok showroom.'
        );
        $this->assertSame(2, Purchase::where('vehicle_id', $vehicle->id)->count());
        $this->assertSame(
            1,
            Vehicle::where('vin', 'VINBUYBACK001')->count(),
            'Buyback tidak boleh menduplikasi kendaraan.'
        );

        // --- Motor muncul lagi di dropdown Penjualan
        $this->assertContains(
            $vehicle->id,
            array_keys($this->vehicleDropdownOptions()),
            'Motor buyback harus muncul kembali di pilihan Motor pada form Penjualan.'
        );

        // --- Jual lagi
        Livewire::test(CreateSale::class)
            ->fillForm([
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'customer_name' => 'Siti',
                'customer_phone' => '08987654321',
                'sale_date' => now()->toDateString(),
                'sale_price' => 12_000_000,
                'payment_method' => 'cash',
                'status' => 'proses',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sale2 = Sale::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $purchase2 = Purchase::where('vehicle_id', $vehicle->id)->latest('id')->first();

        $this->assertNotSame($sale1->id, $sale2->id, 'Penjualan kedua harus terbentuk.');
        $this->assertSame(
            $purchase2->id,
            $sale2->purchase_id,
            'Penjualan siklus 2 harus terikat ke pembelian siklus 2.'
        );

        // --- Laba tiap siklus dihitung terhadap modalnya sendiri
        $this->assertEquals(2_000_000, $sale1->fresh()->laba_kotor, 'Laba siklus 1 = 11jt - 9jt.');
        $this->assertEquals(2_000_000, $sale2->laba_kotor, 'Laba siklus 2 = 12jt - 10jt.');

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);
    }

    // ===========================================================
    // 3. Proteksi: buyback ditolak saat penjualan masih berjalan
    // ===========================================================

    public function test_buyback_ditolak_saat_penjualan_masih_berjalan(): void
    {
        $this->createPurchaseViaUi();
        $vehicle = Vehicle::where('vin', 'VINBUYBACK001')->first();

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => Customer::create(['name' => 'Budi'])->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'kirim',
        ]);

        // Pesan penolakan harus nempel di field Nomor Rangka, bukan hilang tanpa jejak
        Livewire::test(CreatePurchase::class)
            ->fillForm($this->purchaseFormData(['purchase_price' => 10_000_000]))
            ->call('create')
            ->assertHasFormErrors(['vin']);

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status, 'Status tidak boleh berubah saat buyback ditolak.');
        $this->assertSame(1, Purchase::where('vehicle_id', $vehicle->id)->count());
    }

    public function test_motor_dengan_penjualan_berjalan_tidak_muncul_di_dropdown(): void
    {
        $this->createPurchaseViaUi();
        $vehicle = Vehicle::where('vin', 'VINBUYBACK001')->first();

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => Customer::create(['name' => 'Budi'])->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'proses',
        ]);

        $this->assertNotContains(
            $vehicle->id,
            array_keys($this->vehicleDropdownOptions()),
            'Motor yang sedang dijual tidak boleh muncul di pilihan Motor.'
        );
    }

    // ===========================================================
    // 4. Pelanggan lama beli lagi
    // ===========================================================

    public function test_pelanggan_lama_beli_lagi_tanpa_kehilangan_data(): void
    {
        $customer = Customer::create([
            'name' => 'Budi',
            'phone' => '08123456789',
            'nik' => '3204010101900001',
            'address' => 'Jl. Merdeka 1',
            'instagram' => 'budi',
        ]);

        $this->createPurchaseViaUi(['vin' => 'VINLAIN002', 'engine_number' => 'ENGLAIN002']);
        $vehicle = Vehicle::where('vin', 'VINLAIN002')->first();

        Livewire::test(CreateSale::class)
            ->fillForm([
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'customer_name' => 'Budi',
                'customer_phone' => '08123456789',
                'customer_nik' => '3204010101900001',
                'sale_date' => now()->toDateString(),
                'sale_price' => 11_000_000,
                'payment_method' => 'cash',
                'status' => 'proses',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $customer->refresh();

        $this->assertSame(1, Customer::where('name', 'Budi')->count(), 'Customer tidak boleh terduplikasi.');
        $this->assertSame('3204010101900001', $customer->nik, 'NIK tidak boleh hilang.');
        $this->assertSame('Jl. Merdeka 1', $customer->address, 'Alamat tidak boleh hilang.');
        $this->assertSame('budi', $customer->instagram, 'Instagram tidak boleh hilang.');
    }

    // ===========================================================
    // 5. Batal setelah buyback
    // ===========================================================

    public function test_batal_setelah_buyback_mengembalikan_motor_ke_dropdown(): void
    {
        $this->createPurchaseViaUi();
        $vehicle = Vehicle::where('vin', 'VINBUYBACK001')->first();
        $purchase1 = Purchase::where('vehicle_id', $vehicle->id)->first();

        // Siklus 1 tuntas
        Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase1->id,
            'customer_id' => Customer::create(['name' => 'Budi'])->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        // Buyback lewat UI
        $this->createPurchaseViaUi(['purchase_price' => 10_000_000]);
        $vehicle->refresh();
        $this->assertSame('available', $vehicle->status);

        // Siklus 2 dibuat lewat UI lalu dibatalkan
        Livewire::test(CreateSale::class)
            ->fillForm([
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'customer_name' => 'Siti',
                'sale_date' => now()->toDateString(),
                'sale_price' => 12_000_000,
                'payment_method' => 'cash',
                'status' => 'proses',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sale2 = Sale::where('vehicle_id', $vehicle->id)->latest('id')->first();
        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);

        Livewire::test(EditSale::class, ['record' => $sale2->getKey()])
            ->fillForm(['status' => 'cancel'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Motor ada di showroom lagi — sale 'selesai' siklus lama tidak boleh menahannya
        $vehicle->refresh();
        $this->assertSame('available', $vehicle->status);
        $this->assertContains(
            $vehicle->id,
            array_keys($this->vehicleDropdownOptions()),
            'Setelah pembatalan, motor harus muncul lagi di pilihan Motor.'
        );
    }

    /** Opsi dropdown "Motor" persis seperti yang dirender form Penjualan */
    private function vehicleDropdownOptions(): array
    {
        $field = Livewire::test(CreateSale::class)
            ->instance()
            ->form
            ->getFlatFields(withHidden: true)['vehicle_id'] ?? null;

        $this->assertNotNull($field, 'Field vehicle_id tidak ditemukan di form Penjualan.');

        return $field->getOptions();
    }
}
