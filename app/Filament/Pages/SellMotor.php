<?php

namespace App\Filament\Pages;

use App\Models\Brand;
use App\Models\Supplier;
use App\Models\VehicleModel;
use App\Models\Year;
use App\Models\Request as VehicleRequest;
use App\Models\VehiclePhoto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;

class SellMotor extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Form Jual Motor';
    protected static ?string $title = 'Form Jual Motor';
    protected static ?string $slug = 'sell-motor';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('No WhatsApp')
                            ->tel()
                            ->helperText('Contoh: 08xxxxxxxxxx')
                            ->required()
                            ->maxLength(30),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Motor')
                    ->schema([
                        Forms\Components\Select::make('brand_id')
                            ->label('Merk')
                            ->options(fn () => Brand::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('model_id')
                            ->label('Model')
                            ->options(fn ($get) => $get('brand_id')
                                ? VehicleModel::where('brand_id', $get('brand_id'))
                                    ->orderBy('name')->pluck('name', 'id')
                                : [])
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('year_id')
                            ->label('Tahun')
                            ->options(fn () => Year::orderBy('year', 'desc')->pluck('year', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('odometer')
                            ->label('Odometer (KM)')
                            ->numeric()
                            ->rules(['integer','min:0'])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Foto Motor')
                    ->schema([
                        Forms\Components\FileUpload::make('photos')
                            ->label('Upload Foto (maks. 5)')
                            ->multiple()
                            ->maxFiles(5)
                            ->image()
                            ->directory('requests') // simpan di storage/app/public/requests
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        // 1) Supplier (nama + phone)
        $supplier = Supplier::firstOrCreate(
            ['phone' => $state['phone']],
            ['name'  => $state['name']]
        );

        // 2) Buat request (belum jadi vehicle)
        $req = VehicleRequest::create([
            'supplier_id'      => $supplier->id,
            'brand_id'         => $state['brand_id'],
            'vehicle_model_id' => $state['model_id'],
            'year_id'          => $state['year_id'],
            'odometer'         => $state['odometer'],
            'type'             => 'sell',
            'status'           => 'hold',
            'notes'            => sprintf(
                "Pengajuan jual oleh %s (%s), odo %s km",
                $state['name'],
                $state['phone'],
                number_format((int) $state['odometer'])
            ),
        ]);

        // 3) Simpan foto → vehicle_photos.request_id
        $photos = Arr::wrap($state['photos'] ?? []);
        foreach ($photos as $i => $path) {
            VehiclePhoto::create([
                'request_id'  => $req->id,
                'path'        => $path,
                'caption'     => null,
                'photo_order' => $i,
            ]);
        }

        // 4) Reset form + notif sukses
        $this->form->fill([]);
        Notification::make()
            ->title('Data terkirim')
            ->body('Terima kasih! Tim kami akan menghubungi Anda via WhatsApp.')
            ->success()
            ->send();
    }
}
