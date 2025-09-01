<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\Type;
use App\Models\Color;
use App\Models\VehicleModel;
use App\Models\Supplier;
use App\Models\Year;
use App\Models\Request as VehicleRequest;
use App\Models\VehiclePhoto;

class LandingController extends Controller
{
    /**
     * Display the homepage with a list of available vehicles.
     */
    public function index(Request $request)
    {
        // Query dasar: motor status hold / available
        $vehicleQuery = Vehicle::whereIn('status', ['hold', 'available'])
            ->with(['vehicleModel.brand', 'photos', 'type', 'year']);

        // Filter brand
        if ($request->filled('brand')) {
            $vehicleQuery->whereHas('vehicleModel.brand', function ($q) use ($request) {
                $q->where('id', $request->brand);
            });
        }
        // Filter type
        if ($request->filled('type')) {
            $vehicleQuery->where('type_id', $request->type);
        }
        // Filter year
        if ($request->filled('year')) {
            $vehicleQuery->where('year_id', $request->year);
        }
        // Filter harga
        if ($request->filled('price') && $request->price !== 'semua') {
            $range = explode('-', $request->price);
            if (count($range) === 2) {
                $vehicleQuery->whereBetween('sale_price', [$range[0], $range[1]]);
            }
        }

        $vehicles = $vehicleQuery->latest()->paginate(9)->withQueryString();

        $brands = Brand::orderBy('name')->get();
        $types  = Type::orderBy('name')->get();
        $years  = Year::orderBy('year', 'desc')->get();

        $heroSlides = [
            [
                'imageUrl' => "https://gofath.com/motor.jpg",
                'title'    => 'Performa & Adrenalin',
                'subtitle' => 'Temukan Koleksi Motor Sport Terbaik Kami',
            ],
            [
                'imageUrl' => "https://gofath.com/motor.jpg",
                'title'    => 'Kenyamanan & Gaya',
                'subtitle' => 'Jelajahi Pilihan Skuter Matik Modern',
            ],
        ];

        return view('frontend.index', compact('vehicles', 'brands', 'types', 'years', 'heroSlides'));
    }

    /**
     * Display detail page of single vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['vehicleModel.brand', 'type', 'color', 'photos', 'year']);
        return view('frontend.show', compact('vehicle'));
    }

    /**
     * Form untuk user jual motor.
     */
    public function sellForm()
    {
        $brands = Brand::orderBy('name')->get();
        $types  = Type::orderBy('name')->get();
        $years  = Year::orderBy('year', 'desc')->get();

        $heroSlides = [
            [
                'imageUrl' => "https://gofath.com/motor.jpg",
                'title'    => 'Jual Motor Kamu',
                'subtitle' => 'Isi form, kami hubungi segera',
            ],
            [
                'imageUrl' => "https://gofath.com/motor.jpg",
                'title'    => 'Proses Mudah',
                'subtitle' => 'Gratis inspeksi & penjemputan*',
            ],
        ];

        return view('frontend.sell-form', compact('brands', 'types', 'years', 'heroSlides'));
    }

    /**
     * Proses submit form jual motor → masuk ke suppliers, years, requests, vehicle_photos.
     */
    public function sellSubmit(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'brand_id'         => 'required|exists:brands,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'year'             => 'required|digits:4',
            'license_plate'    => 'required|string|max:15',
            'odometer'         => 'nullable|integer|min:0',
            'notes'            => 'nullable|string',
            'photos'           => ['nullable','array','max:5'],
            'photos.*'         => ['file','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // 1) Supplier (name + WA)
        $supplier = Supplier::firstOrCreate(
            ['phone' => $request->phone],
            ['name'  => $request->name]
        );

        // 2) Year (pastikan ada di master)
        $year = Year::firstOrCreate(['year' => $request->year]);

        // 3) Buat Request
        $req = VehicleRequest::create([
            'supplier_id'      => $supplier->id,
            'brand_id'         => $request->brand_id,
            'vehicle_model_id' => $request->vehicle_model_id,
            'year_id'          => $year->id,
            'odometer'         => $request->odometer,
            'type'             => 'sell',
            'status'           => 'hold',
            'notes'            => "Plat: {$request->license_plate}. ".($request->notes ?? ''),
        ]);

        // 4) Foto (jika diupload)
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store("requests/{$req->id}", 'public');
                VehiclePhoto::create([
                    'request_id'  => $req->id,
                    'path'        => $path,
                    'photo_order' => $i,
                ]);
            }
        }

        return redirect()->route('landing.sell.form')
            ->with('success', 'Terima kasih! Data penjualan kamu sudah kami terima. Kami akan menghubungi via WhatsApp.');
    }

    /**
     * Ajax ambil model by brand
     */
    public function modelsByBrand(Brand $brand)
    {
        return VehicleModel::where('brand_id', $brand->id)
            ->orderBy('name')
            ->get(['id','name']);
    }
}
