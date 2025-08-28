<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\Type;
use App\Models\Color;
use App\Models\VehicleModel;
use App\Models\Purchase;
use App\Models\Supplier;

class LandingController extends Controller
{
    /**
     * Display the homepage with a list of available vehicles.
     */
    public function index()
    {
        $vehicles = Vehicle::where('status', 'available')
            ->with(['vehicleModel.brand', 'photos']) // Eager load relationships for efficiency
            ->latest()
            ->paginate(9); // Show 9 vehicles per page

        return view('frontend.index', compact('vehicles'));
    }

    /**
     * Display the detail page for a single vehicle.
     * Note: We use Route Model Binding here (Vehicle $vehicle).
     */
    public function show(Vehicle $vehicle)
    {
        // The vehicle is automatically fetched by Laravel, or it will throw a 404 error if not found.
        $vehicle->load(['vehicleModel.brand', 'type', 'color', 'photos']);

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
        $vehicleModels = VehicleModel::orderBy('name')->get();

        return view('frontend.sell', compact('brands', 'types', 'vehicleModels'));
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
