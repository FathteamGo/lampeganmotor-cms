<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query()
            ->where('status', 'available')
            ->with(['vehicleModel.brand', 'type', 'photos']);

        // Filtering logic
        if ($request->filled('brand_id') && $request->brand_id !== 'semua') {
            $query->whereHas('vehicleModel', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        if ($request->filled('type_id') && $request->type_id !== 'semua') {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('year') && $request->year !== 'semua') {
            $query->where('year', $request->year);
        }

        if ($request->filled('price_range') && $request->price_range !== 'semua') {
            [$min, $max] = explode('-', $request->price_range);
            $query->whereBetween('price', [(int)$min, (int)$max]);
        }

        $vehicles = $query->latest()->paginate(9);

        return VehicleResource::collection($vehicles);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['vehicleModel.brand', 'type', 'color', 'photos', 'purchases.supplier']);
        return new VehicleResource($vehicle);
    }
}
