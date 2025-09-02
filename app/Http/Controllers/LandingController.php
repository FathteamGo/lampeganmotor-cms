<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\Type;
use App\Models\VehicleModel;
use App\Models\Supplier;
use App\Models\Year;
// use App\Models\Request;
use App\Models\Request as VehicleRequest;
use App\Models\VehiclePhoto;
use App\Services\WhatsAppService;

class LandingController extends Controller
{
    /**
     * Display the homepage with a list of available vehicles.
     */
    public function index(Request $request)
    {
        $vehicleQuery = Vehicle::whereIn('status', ['available'])
            ->with(['vehicleModel.brand', 'photos', 'type', 'year']);

        if ($request->filled('brand')) {
            $vehicleQuery->whereHas('vehicleModel.brand', function ($q) use ($request) {
                $q->where('id', $request->brand);
            });
        }
        if ($request->filled('type')) {
            $vehicleQuery->where('type_id', $request->type);
        }
        if ($request->filled('year')) {
            $vehicleQuery->where('year_id', $request->year);
        }
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
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'brand_name'   => 'required|string|max:255',
            'model_name'   => 'required|string|max:255',
            'year'         => 'required|digits:4',
            'odometer'     => 'nullable|integer|min:0',
            'license_plate'=> 'required|string|max:15',
            'notes'        => 'nullable|string',
            'photos'       => ['nullable','array','max:5'],
            'photos.*'     => ['file','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // 1) Supplier (penjual)
        $supplier = Supplier::firstOrCreate(
            ['phone' => $request->phone],
            ['name'  => $request->name]
        );

        // 2) Brand & Model
        $brand = Brand::firstOrCreate(['name' => trim($request->brand_name)]);
        $model = VehicleModel::firstOrCreate(['brand_id' => $brand->id, 'name' => trim($request->model_name)]);

        // 3) Year
        $year  = Year::firstOrCreate(['year' => (int) $request->year]);

        // 4) Simpan LEAD
        $lead = VehicleRequest::create([
            'supplier_id'      => $supplier->id,
            'brand_id'         => $brand->id,
            'vehicle_model_id' => $model->id,
            'year_id'          => $year->id,
            'odometer'         => $request->odometer,
            'license_plate'    => $request->license_plate,
            'notes'            => $request->notes,
            'type'             => 'sell',
            'status'           => 'hold',
        ]);

        // 5) Foto
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store("requests/{$lead->id}", 'public');
                VehiclePhoto::create([
                    'request_id'  => $lead->id,
                    'path'        => $path,
                    'photo_order' => $i,
                ]);
            }
        }

        // 6) Kirim WhatsApp (non-blocking; kalau gagal tetap lanjut)
        try {
            $wa = app(WhatsAppService::class);

            $title = "{$brand->name} {$model->name} {$year->year}";
            $plate = $request->license_plate;
            $odo   = $request->odometer ? number_format((int)$request->odometer, 0, ',', '.') . ' km' : '-';

            // Ke penjual (supplier)
            $msgSupplier =
                "Halo {$supplier->name}, terima kasih sudah mengajukan Jual Motor ke Lampegan Motor.\n\n".
                "Detail unit:\n".
                "- Unit: {$title}\n".
                "- Plat: {$plate}\n".
                "- Odometer: {$odo}\n".
                "- Catatan: ".($request->notes ?: '-')."\n\n".
                "Tim kami akan menghubungi Anda via WhatsApp untuk proses selanjutnya 🙏";
            $wa->sendText($supplier->phone, $msgSupplier);

            // Ke owner (admin)
            $owner = config('services.wa_gateway.owner');
            if ($owner) {
                $msgOwner =
                    "📥 *Request Jual Masuk*\n\n".
                    "Nama: {$supplier->name}\n".
                    "WA: {$supplier->phone}\n".
                    "Unit: {$title}\n".
                    "Plat: {$plate}\n".
                    "Odometer: {$odo}\n".
                    "Request ID: #{$lead->id}\n".
                    "Catatan: ".($request->notes ?: '-');
                $wa->sendText($owner, $msgOwner);
            }
        } catch (\Throwable $e) {
            // boleh di-log kalau mau:
            // \Log::warning('WA notify failed: '.$e->getMessage());
        }

        return redirect()
            ->route('landing.sell.form')
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
