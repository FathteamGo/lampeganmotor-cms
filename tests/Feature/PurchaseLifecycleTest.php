<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;
use App\Models\Vehicle;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PurchaseLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Master tables
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
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('status')->default('hold');
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
            $table->unsignedBigInteger('supplier_id');
            $table->date('purchase_date');
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Sales table for simulating sold vehicles
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('sale_date')->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('years');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('types');
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('brands');

        parent::tearDown();
    }

    public function test_new_vehicle_purchase_sets_status_available()
    {
        $brand = Brand::firstOrCreate(['name' => 'TestBrand']);
        $model = VehicleModel::firstOrCreate(['name' => 'TestModel', 'brand_id' => $brand->id]);
        $type = Type::firstOrCreate(['name' => 'Matic']);
        $color = Color::firstOrCreate(['name' => 'Hitam']);
        $year = Year::firstOrCreate(['year' => now()->year]);

        // Vehicle baru dengan status hold
        $vehicle = Vehicle::create([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => 'NEWVIN123456',
            'engine_number' => 'NEWENGINE123',
            'purchase_price' => 12000000,
            'status' => 'hold',
        ]);

        $supplier = Supplier::create(['name' => 'Supplier A']);

        $purchase = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_price' => 12000000,
            'notes' => 'Pembelian kendaraan baru',
        ]);

        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'vehicle_id' => $vehicle->id]);

        $vehicle->refresh();
        $this->assertEquals('available', $vehicle->status);
    }

    public function test_repurchase_of_sold_vehicle_sets_status_available_and_increments_purchases()
    {
        $brand = Brand::firstOrCreate(['name' => 'TestBrand']);
        $model = VehicleModel::firstOrCreate(['name' => 'TestModel', 'brand_id' => $brand->id]);
        $type = Type::firstOrCreate(['name' => 'Matic']);
        $color = Color::firstOrCreate(['name' => 'Hitam']);
        $year = Year::firstOrCreate(['year' => now()->year]);

        // Buat kendaraan dan simulasikan pembelian awal
        $vehicle = Vehicle::create([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => 'USEDVIN123456',
            'engine_number' => 'USEDENGINE123',
            'purchase_price' => 9000000,
            'status' => 'available',
        ]);

        $supplier = Supplier::create(['name' => 'Supplier A']);

        // Pembelian awal
        $purchase1 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonths(6),
            'total_price' => 9000000,
            'notes' => 'Pembelian awal',
        ]);

        // Kendaraan terjual ke customer (sale)
        $sale = Sale::create([
            'vehicle_id' => $vehicle->id,
            'sale_date' => now()->subMonths(3),
            'sale_price' => 11000000,
            'status' => 'completed',
        ]);

        $vehicle->refresh();
        $this->assertEquals('sold', $vehicle->status);

        // Sekarang dealer membeli kembali kendaraan yang sudah terjual
        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_price' => 10000000,
            'notes' => 'Pembelian kembali dari customer',
        ]);

        $this->assertDatabaseHas('purchases', ['id' => $purchase2->id, 'vehicle_id' => $vehicle->id]);

        $vehicle->refresh();
        $this->assertEquals('available', $vehicle->status);

        $this->assertEquals(2, Purchase::where('vehicle_id', $vehicle->id)->count());
    }
}
