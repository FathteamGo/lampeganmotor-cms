<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Type;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Siklus jual-beli berulang (buyback).
 *
 * Motor dibeli dari supplier → dijual ke customer → dibeli kembali dari customer
 * → dijual lagi. Sebelumnya alur ini terkunci karena sale berstatus 'selesai'
 * diperlakukan sebagai penjualan aktif yang mengunci motor.
 *
 * Tidak memakai RefreshDatabase karena sebagian migrasi project memakai sintaks
 * MySQL yang tidak didukung SQLite — schema minimal dibuat manual di setUp().
 */
class VehicleResellCycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function makeVehicle(array $attributes = []): Vehicle
    {
        $brand = Brand::firstOrCreate(['name' => 'Honda']);
        $model = VehicleModel::firstOrCreate(['name' => 'Beat', 'brand_id' => $brand->id]);
        $type = Type::firstOrCreate(['name' => 'Matic']);
        $color = Color::firstOrCreate(['name' => 'Hitam']);
        $year = Year::firstOrCreate(['year' => 2020]);

        return Vehicle::create(array_merge([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => 'VIN' . uniqid(),
            'engine_number' => 'ENG' . uniqid(),
            'purchase_price' => 9_000_000,
            'status' => 'available',
        ], $attributes));
    }

    // =======================
    // Definisi "penjualan aktif"
    // =======================

    public function test_sale_proses_mengunci_motor(): void
    {
        $vehicle = $this->makeVehicle();

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'proses',
        ]);

        $vehicle->refresh();

        $this->assertSame('sold', $vehicle->status);
        $this->assertNotNull($vehicle->runningSale());
        $this->assertFalse($vehicle->isSellable());
    }

    public function test_sale_selesai_bukan_penjualan_berjalan(): void
    {
        $vehicle = $this->makeVehicle();

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        $vehicle->refresh();

        // Motor tetap 'sold' (masih di tangan customer), tapi tidak ada
        // penjualan berjalan yang perlu diselesaikan lebih dulu.
        $this->assertSame('sold', $vehicle->status);
        $this->assertNull($vehicle->runningSale());
    }

    // =======================
    // Siklus buyback
    // =======================

    public function test_motor_buyback_bisa_dijual_kembali(): void
    {
        $vehicle = $this->makeVehicle();
        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);
        $supplier = Supplier::create(['name' => 'Supplier A']);

        Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        // Siklus 1: terjual dan tuntas
        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);

        // Buyback: tidak ada penjualan berjalan, jadi motor boleh kembali jadi stok
        $this->assertNull($vehicle->runningSale());

        Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subDay(),
            'total_price' => 10_000_000,
        ]);
        $vehicle->update(['status' => 'available']);

        $vehicle->refresh();
        $this->assertTrue($vehicle->isSellable());

        // Siklus 2: dijual lagi
        $sale2 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'status' => 'proses',
        ]);

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);
        $this->assertSame($sale2->id, $vehicle->runningSale()->id);
        $this->assertSame(2, $vehicle->purchases()->count());
        $this->assertSame(2, $vehicle->sales()->count());
    }

    public function test_buyback_ditolak_saat_penjualan_masih_berjalan(): void
    {
        $vehicle = $this->makeVehicle();

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'kirim',
        ]);

        $vehicle->refresh();

        // Guard yang dipakai CreatePurchase untuk menolak buyback
        $this->assertNotNull($vehicle->runningSale());
    }

    // =======================
    // Modal yang benar untuk laba
    // =======================

    public function test_purchase_untuk_sale_kedua_memakai_pembelian_terbaru(): void
    {
        $vehicle = $this->makeVehicle();
        $supplier = Supplier::create(['name' => 'Supplier A']);

        $purchase1 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subDay(),
            'total_price' => 10_000_000,
        ]);

        // Sale siklus 1 harus tetap mengacu ke pembelian pertama
        $this->assertSame(
            $purchase1->id,
            $vehicle->purchaseForSaleDate(now()->subMonths(3))->id
        );

        // Sale siklus 2 harus mengacu ke pembelian kedua
        $this->assertSame(
            $purchase2->id,
            $vehicle->purchaseForSaleDate(now())->id
        );
    }

    public function test_sale_backdate_memakai_pembelian_terbaru_bukan_tertua(): void
    {
        $vehicle = $this->makeVehicle();
        $supplier = Supplier::create(['name' => 'Supplier A']);

        Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_price' => 10_000_000,
        ]);

        // Tidak ada pembelian yang mendahului tanggal jual → pakai yang TERBARU,
        // bukan harga beli dari siklus yang sudah lewat.
        $this->assertSame(
            $purchase2->id,
            $vehicle->purchaseForSaleDate(now()->subYear())->id
        );
    }

    public function test_laba_kotor_dihitung_terhadap_pembelian_yang_terikat(): void
    {
        $vehicle = $this->makeVehicle();
        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);
        $supplier = Supplier::create(['name' => 'Supplier A']);

        $purchase1 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subDay(),
            'total_price' => 10_000_000,
        ]);

        $sale1 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'purchase_id' => $purchase1->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);

        $sale2 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'purchase_id' => $purchase2->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'payment_method' => 'cash',
            'status' => 'proses',
        ]);

        $this->assertEquals(2_000_000, $sale1->laba_kotor);
        $this->assertEquals(2_000_000, $sale2->laba_kotor);
    }

    // =======================
    // Status motor saat sale dibatalkan
    // =======================

    public function test_sale_dibatalkan_mengembalikan_motor_ke_available(): void
    {
        $vehicle = $this->makeVehicle();

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'proses',
        ]);

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);

        $sale->update(['status' => 'cancel']);

        $vehicle->refresh();
        $this->assertSame('available', $vehicle->status);
    }

    public function test_status_khusus_tidak_ditimpa_saat_sale_dibatalkan(): void
    {
        $vehicle = $this->makeVehicle(['status' => 'in_repair']);

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'cancel',
        ]);

        $sale->touch();

        $vehicle->refresh();
        $this->assertSame('in_repair', $vehicle->status);
    }

    public function test_sale_selesai_tidak_membuat_motor_available_saat_sale_lain_dibatalkan(): void
    {
        $vehicle = $this->makeVehicle();

        $customer = Customer::firstOrCreate(['name' => 'Test Customer']);

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        $batal = Sale::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'status' => 'proses',
        ]);

        $batal->update(['status' => 'cancel']);

        $vehicle->refresh();

        // Masih ada sale 'selesai' → motor belum di-buyback, jangan jadi available
        $this->assertSame('sold', $vehicle->status);
    }
}
