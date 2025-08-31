<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\Type;
use App\Models\Color;
use App\Models\VehicleModel;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Year;

class LandingController extends Controller
{
    /**
     * Display the homepage with a list of available vehicles.
     */
    public function index(Request $request)
    {
        // Query dasar untuk mengambil motor yang tersedia
        $vehicleQuery = Vehicle::where('status', 'hold')->with(['vehicleModel.brand', 'photos', 'type', 'year']);
        // $vehicleQuery = Vehicle::where('status', 'available')->with(['vehicleModel.brand', 'photos', 'type', 'year']);

        // --- Menerapkan Filter ---
        if ($request->filled('brand')) {
            $vehicleQuery->whereHas('vehicleModel.brand', function ($query) use ($request) {
                $query->where('id', $request->brand);
            });
        }
        if ($request->filled('type')) {
            $vehicleQuery->where('type_id', $request->type);
        }
        if ($request->filled('year')) {
            $vehicleQuery->where('year_id', $request->year);
        }
        if ($request->filled('price') && $request->price != 'semua') {
            $priceRange = explode('-', $request->price);
            $vehicleQuery->whereBetween('sale_price', [$priceRange[0], $priceRange[1]]);
        }
        // --- Akhir Filter ---

        $vehicles = $vehicleQuery->latest()->paginate(9)->withQueryString();

        // Data untuk mengisi dropdown filter
        $brands = Brand::orderBy('name')->get();
        $types = Type::orderBy('name')->get();
        $years = Year::orderBy('year', 'desc')->get();

        // Data untuk Hero Slider (bisa juga diambil dari database)
        $heroSlides = [
            [
                'imageUrl' => "https://gofath.com/motor.jpg", // Ganti dengan gambar Anda
                'title' => 'Performa & Adrenalin',
                'subtitle' => 'Temukan Koleksi Motor Sport Terbaik Kami'
            ],
            [
                'imageUrl' => "https://gofath.com/motor.jpg", // Ganti dengan gambar Anda
                'title' => 'Kenyamanan & Gaya',
                'subtitle' => 'Jelajahi Pilihan Skuter Matik Modern'
            ],
        ];

        return view('frontend.index', compact('vehicles', 'brands', 'types', 'years', 'heroSlides'));
    }

    /**
     * Display the detail page for a single vehicle.
     * Note: We use Route Model Binding here (Vehicle $vehicle).
     */
    public function show(Vehicle $vehicle)
    {
        // The vehicle is automatically fetched by Laravel, or it will throw a 404 error if not found.
        $vehicle->load(['vehicleModel.brand', 'type', 'color', 'photos', 'year']);

        return view('frontend.show', compact('vehicle'));
    }

    /**
     * Display the form for a customer to sell their motorcycle.
     */
    public function sellForm()
    {
        // Send master data to populate the form's dropdowns
        $brands = Brand::orderBy('name')->get();
        $types = Type::orderBy('name')->get();
        // $vehicleModels = VehicleModel::orderBy('name')->get(); // This is not ideal.
        // Vehicle models should be loaded dynamically via AJAX based on the selected brand
        // to prevent invalid combinations and improve user experience.
        return view('frontend.sell', compact('brands', 'types'));
    }

    /**
     * Process the submission from the "sell your motorcycle" form.
     */
    public function sellSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'brand_id' => 'required|exists:brands,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'year' => 'required|digits:4',
            'license_plate' => 'required|string|max:15',
            'notes' => 'nullable|string',
        ]);

        // Logic: A customer selling their motorcycle is treated as a 'Supplier'.
        // We'll first check if a supplier with this phone number already exists.
        $supplier = Supplier::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name]
        );

        // For now, we will just redirect with a success message.
        // The actual logic for saving the submission (e.g., sending an email notification
        // to the admin) can be expanded here.

        return redirect()->route('landing.sell.form')->with('success', 'Thank you! We have received your offer. Our team will contact you shortly.');
    }
}
