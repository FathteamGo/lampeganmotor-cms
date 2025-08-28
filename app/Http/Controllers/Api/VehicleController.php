<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        // Ambil hanya motor yang tersedia, dan load relasi yg dibutuhkan
        $vehicles = Vehicle::where('status', 'available')
            ->with(['vehicleModel.brand', 'type', 'color', 'photos'])
            ->latest()
            ->paginate(12);

        return response()->json($vehicles);
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['vehicleModel.brand', 'type', 'color', 'photos', 'additionalCosts'])
            ->findOrFail($id);

        return response()->json($vehicle);
    }
}
