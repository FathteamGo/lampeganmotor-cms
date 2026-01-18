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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PurchaseCanShareVehicleTest extends TestCase
{
    // Tidak menggunakan RefreshDatabase karena beberapa migration memakai sintaks MySQL yang tidak
    // didukung SQLite di environment testing. Kita akan membuat schema minimal di setUp()/tearDown().

    protected function setUp(): void
    {
        parent::setUp();

        // Create minimal master tables
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
            $table->decimal('purchase_price', 15, 2);
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
    }

    protected function tearDown(): void
    {
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

    public function test_multiple_purchases_can_reference_the_same_vehicle()
    {
        // Buat master data yang diperlukan
        $brand = Brand::firstOrCreate(['name' => 'TestBrand']);
        $model = VehicleModel::firstOrCreate(['name' => 'TestModel', 'brand_id' => $brand->id]);
        $type = Type::firstOrCreate(['name' => 'Matic']);
        $color = Color::firstOrCreate(['name' => 'Hitam']);
        $year = Year::firstOrCreate(['year' => now()->year]);

        // Buat kendaraan
        $vehicle = Vehicle::create([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => 'TESTVIN1234567890',
            'engine_number' => 'TESTENGINE123',
            'purchase_price' => 10000000,
            'status' => 'hold',
        ]);

        // Buat supplier
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        // Buat dua purchase yang menunjuk ke kendaraan yang sama
        $purchase1 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_price' => 10000000,
            'notes' => 'Pembelian pertama',
        ]);

        $purchase2 = Purchase::create([
            'vehicle_id' => $vehicle->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_price' => 11000000,
            'notes' => 'Pembelian kedua',
        ]);

        // Assertions
        $this->assertDatabaseHas('purchases', ['id' => $purchase1->id, 'vehicle_id' => $vehicle->id]);
        $this->assertDatabaseHas('purchases', ['id' => $purchase2->id, 'vehicle_id' => $vehicle->id]);
        $this->assertEquals(2, Purchase::where('vehicle_id', $vehicle->id)->count());
    }
}
