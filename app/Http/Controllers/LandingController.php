<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\HeaderSetting;
use App\Models\Type;
use App\Models\VehicleModel;
use App\Models\Supplier;
use App\Models\Year;
use App\Models\Request as VehicleRequest;
use App\Models\VehiclePhoto;
use App\Models\HeroSlide;
use App\Services\WhatsAppService;
use GuzzleHttp\Psr7\Header;

class LandingController extends Controller
{
    /**
     * Halaman utama (Landing Page)
     */
    public function index(Request $request)
    {

          DB::table('visitors')->insert([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url'        => '/',
            'visited_at' => now(),
        ]);

        $vehicleQuery = Vehicle::whereIn('status', ['available'])
            ->with(['vehicleModel.brand', 'photos', 'type', 'year']);

        // filter kendaraan
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

        // --- amanin kolom order ---
        $heroSlides = HeroSlide::orderBy('order_column', 'asc')->get();

        // fallback kalau kosong → kasih dummy data
        if ($heroSlides->isEmpty()) {
        $heroSlides = collect([
            (object)[
                'image'    => "https://via.placeholder.com/1200x600?text=Slide+1",
                'title'    => 'Performa & Adrenalin',
                'subtitle' => 'Temukan Koleksi Motor Sport Terbaik Kami',
            ],
            (object)[
                'image'    => "https://via.placeholder.com/1200x600?text=Slide+2",
                'title'    => 'Kenyamanan & Gaya',
                'subtitle' => 'Jelajahi Pilihan Skuter Matik Modern',
            ],
         ]);
    }

    $header = HeaderSetting::first();

    if (!$header) {
        $header = (object) [
            'site_name'     => 'Lampegan Motor',
            'logo'          => null, // nanti fallback ke default.png di blade
            'instagram_url' => 'https://www.instagram.com/lampeganmotorbdg',
            'tiktok_url'    => 'https://www.tiktok.com/@lampeganmotorbdg',
        ];
    }


        return view('frontend.index', compact('vehicles', 'brands', 'types', 'years', 'heroSlides','header'));
    }

    /**
     * Halaman detail kendaraan
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['vehicleModel.brand', 'type', 'color', 'photos', 'year']);
        return view('frontend.show', compact('vehicle'));
    }

    /**
     * Form jual motor
     */
    public function sellForm()
    {
        $brands = Brand::orderBy('name')->select('id','name')->get();
        $types  = Type::orderBy('name')->select('id','name')->get();
        $years  = Year::orderBy('year', 'desc')->select('id','year')->get();

         $header = HeaderSetting::first();
            if (! $header) {
                $header = (object) [
                    'site_name'     => 'Lampegan Motor',
                    'logo'          => null,
                    'instagram_url' => 'https://www.instagram.com/lampeganmotorbdg',
                    'tiktok_url'    => 'https://www.tiktok.com/@lampeganmotorbdg',
                ];
            }
        $heroSlides = HeroSlide::orderBy('order_column', 'asc')->get();

        return view('frontend.sell-form', compact('brands', 'types', 'years', 'heroSlides','header'));
    }

    /**
     * Submit form jual motor
     */
    public function sellSubmit(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required','string','max:255'],
            'phone'            => ['required','string','max:20'],
            'brand_id'         => ['required', Rule::exists('brands','id')],
            'vehicle_model_id' => ['required', Rule::exists('vehicle_models','id')],
            'year_id'          => ['required', Rule::exists('years','id')],
            'license_plate'    => ['required','string','max:15'],
            'odometer'         => ['nullable','integer','min:0'],
            'notes'            => ['nullable','string'],
            'photos'           => ['nullable','array','max:5'],
            'photos.*'         => ['file','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Supplier
            $supplier = Supplier::firstOrCreate(
                ['phone' => $validated['phone']],
                ['name'  => $validated['name']]
            );

            // Request jual motor
            $lead = VehicleRequest::create([
                'supplier_id'      => $supplier->id,
                'brand_id'         => $validated['brand_id'],
                'vehicle_model_id' => $validated['vehicle_model_id'],
                'year_id'          => $validated['year_id'],
                'odometer'         => $validated['odometer'] ?? null,
                'license_plate'    => $validated['license_plate'],
                'notes'            => $validated['notes'] ?? null,
                'type'             => 'sell',
                'status'           => 'hold',
            ]);

            // Foto
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $i => $file) {
                    if (!$file) continue;
                    $path = $file->store("requests/{$lead->id}", 'public');
                    VehiclePhoto::create([
                        'request_id'  => $lead->id,
                        'path'        => $path,
                        'photo_order' => $i,
                    ]);
                }
            }

            // Notifikasi WA (opsional)
            try {
                $wa = app(WhatsAppService::class);
                $brand = Brand::find($validated['brand_id']);
                $model = VehicleModel::find($validated['vehicle_model_id']);
                $year  = Year::find($validated['year_id']);

                $title = "{$brand->name} {$model->name} {$year->year}";
                $plate = $validated['license_plate'];
                $odo   = isset($validated['odometer'])
                    ? number_format((int)$validated['odometer'], 0, ',', '.') . ' km'
                    : '-';

                $msgSupplier =
                    "Halo {$supplier->name}, terima kasih sudah mengajukan Jual Motor ke Lampegan Motor.\n\n".
                    "Detail unit:\n".
                    "- Unit: {$title}\n".
                    "- Plat: {$plate}\n".
                    "- Odometer: {$odo}\n".
                    "- Catatan: ".($validated['notes'] ?? '-')."\n\n".
                    "Tim kami akan menghubungi Anda via WhatsApp untuk proses selanjutnya 🙏";
                $wa->sendText($supplier->phone, $msgSupplier);

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
                        "Catatan: ".($validated['notes'] ?? '-');
                    $wa->sendText($owner, $msgOwner);
                }
            } catch (\Throwable $e) {
                // log optional
            }
        });

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
