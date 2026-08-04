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
use Illuminate\Database\Schema\Blueprint;
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
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('years', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models');
            $table->foreignId('type_id')->constrained('types');
            $table->foreignId('color_id')->constrained('colors');
            $table->foreignId('year_id')->constrained('years');
            $table->string('vin')->unique();
            $table->string('engine_number')->unique();
            $table->string('license_plate')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('purchase_date');
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->string('category')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('sale_date')->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('payment_method')->default('cash');
            $table->decimal('dp_po', 15, 2)->nullable();
            $table->decimal('dp_real', 15, 2)->nullable();
            $table->decimal('payment_to_customer', 15, 2)->nullable();
            $table->decimal('cmo_fee', 15, 2)->nullable();
            $table->decimal('direct_commission', 15, 2)->nullable();
            $table->string('status')->default('proses');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach ([
            'sales', 'purchase_additional_costs', 'purchases', 'suppliers',
            'customers', 'vehicles', 'years', 'colors', 'types',
            'vehicle_models', 'brands',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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

        Sale::create([
            'vehicle_id' => $vehicle->id,
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

        Sale::create([
            'vehicle_id' => $vehicle->id,
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

        Sale::create([
            'vehicle_id' => $vehicle->id,
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
            'purchase_id' => $purchase1->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);

        $sale2 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase2->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'payment_method' => 'cash',
            'status' => 'proses',
        ]);

        $this->assertEquals(2_000_000, $sale1->laba_kotor);
        $this->assertEquals(2_000_000, $sale2->laba_kotor);
    }

    public function test_hapus_purchase_mengikat_ulang_sale_ke_pembelian_tersisa(): void
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

        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase2->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'payment_method' => 'cash',
            'status' => 'proses',
        ]);

        // Purchase salah input lalu dihapus — sale tidak boleh kehilangan modalnya
        $purchase2->delete();

        $sale->refresh();

        $this->assertSame($purchase1->id, $sale->purchase_id);
        $this->assertEquals(3_000_000, $sale->laba_kotor);
    }

    public function test_sales_report_export_memakai_modal_siklus_yang_benar(): void
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

        // Biaya tambahan siklus 2 (STNK dll) — harus ikut jadi modal
        \Illuminate\Support\Facades\DB::table('purchase_additional_costs')->insert([
            'purchase_id' => $purchase2->id,
            'category' => 'STNK',
            'price' => 500_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Buyback menimpa vehicles.purchase_price dengan harga siklus terbaru
        $vehicle->update(['purchase_price' => 10_000_000, 'status' => 'available']);

        $sale1 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase1->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);

        $sale2 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase2->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'payment_method' => 'cash',
            'status' => 'proses',
        ]);

        $export = new \App\Exports\SalesReportExport();

        $row1 = $export->map($sale1->fresh());
        $row2 = $export->map($sale2->fresh());

        // Kolom 12 = H TOTAL PEMBELIAN, kolom 18 = LABA BERSIH
        // Sale siklus 1 pakai modal siklus 1 (9jt), bukan 10jt milik siklus 2
        $this->assertEquals(9_000_000, $row1[12]);
        $this->assertEquals(2_000_000, $row1[18]);

        // Sale siklus 2 pakai modal siklus 2 termasuk biaya tambahan (10jt + 500rb)
        $this->assertEquals(10_500_000, $row2[12]);
        $this->assertEquals(1_500_000, $row2[18]);
    }

    // =======================
    // Status motor saat sale dibatalkan
    // =======================

    public function test_sale_dibatalkan_mengembalikan_motor_ke_available(): void
    {
        $vehicle = $this->makeVehicle();

        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
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

        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
            'sale_date' => now(),
            'sale_price' => 11_000_000,
            'status' => 'cancel',
        ]);

        $sale->touch();

        $vehicle->refresh();
        $this->assertSame('in_repair', $vehicle->status);
    }

    public function test_sale_setelah_buyback_dibatalkan_mengembalikan_motor_ke_available(): void
    {
        $vehicle = $this->makeVehicle();
        $supplier = Supplier::create(['name' => 'Supplier A']);

        $purchase1 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        // Siklus 1: terjual dan tuntas
        Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase1->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        // Buyback
        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subDay(),
            'total_price' => 10_000_000,
        ]);
        $vehicle->update(['status' => 'available']);

        // Siklus 2 dibuat lalu dibatalkan
        $sale2 = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase2->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'status' => 'proses',
        ]);

        $vehicle->refresh();
        $this->assertSame('sold', $vehicle->status);

        $sale2->update(['status' => 'cancel']);

        // Motor ada di showroom lagi. Sale 'selesai' siklus 1 tidak boleh
        // menahannya di 'sold' — itu riwayat kepemilikan yang sudah lewat.
        $vehicle->refresh();
        $this->assertSame('available', $vehicle->status);
        $this->assertTrue($vehicle->isSellable());
    }

    public function test_sale_selesai_siklus_berjalan_tetap_menahan_motor_di_sold(): void
    {
        $vehicle = $this->makeVehicle();
        $supplier = Supplier::create(['name' => 'Supplier A']);

        $purchase = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9_000_000,
        ]);

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        // Sale kedua di siklus yang SAMA (belum ada buyback) lalu dibatalkan
        $batal = Sale::create([
            'vehicle_id' => $vehicle->id,
            'purchase_id' => $purchase->id,
            'sale_date' => now(),
            'sale_price' => 12_000_000,
            'status' => 'proses',
        ]);

        $batal->update(['status' => 'cancel']);

        $vehicle->refresh();

        // Belum ada buyback → motor masih di tangan customer, tetap 'sold'
        $this->assertSame('sold', $vehicle->status);
    }

    public function test_sale_selesai_tidak_membuat_motor_available_saat_sale_lain_dibatalkan(): void
    {
        $vehicle = $this->makeVehicle();

        Sale::create([
            'vehicle_id' => $vehicle->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11_000_000,
            'status' => 'selesai',
        ]);

        $batal = Sale::create([
            'vehicle_id' => $vehicle->id,
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
