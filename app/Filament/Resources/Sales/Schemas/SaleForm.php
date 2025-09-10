<?php
namespace App\Filament\Resources\Sales\Schemas;

use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Sales')
                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('vehicle_id')
                ->label(__('tables.purchase_model'))
                ->options(
                    Vehicle::with(['vehicleModel', 'color'])
                        ->get()
                        ->mapWithKeys(fn ($v) => [
                            $v->id => sprintf(
                                '%s | %s | %s',
                                $v->vehicleModel->name ?? 'Unknown Model',
                                $v->color->name ?? 'Unknown Color',
                                $v->license_plate ?? 'No Plate'
                            ),
                        ])
                )
                ->searchable()
                ->required(),

            Select::make('customer_id')
                ->label(__('tables.customer'))
                ->options(Customer::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            DatePicker::make('sale_date')
                ->label(__('tables.sale_date'))
                ->required()
                ->default(now()),

            TextInput::make('sale_price')
                ->label(__('tables.sale_price'))
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->required(),

            Select::make('payment_method')
                ->label(__('tables.payment_method'))
                ->options([
                    'cash'        => 'Cash',
                    'credit'      => 'Credit',
                    'tukartambah' => 'Tukar Tambah',
                    'cash_tempo'  => 'Cash Tempo',
                ])
                ->default('cash')
                ->required()
                ->reactive(),

            TextInput::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->visible(fn ($get) => $get('payment_method') === 'cash_tempo'),

            DatePicker::make('due_date')
                ->label('Tanggal Jatuh Tempo')
                ->visible(fn ($get) => $get('payment_method') === 'cash_tempo'),

            TextInput::make('ig')
                ->label('Instagram')
                ->placeholder('@username')
                ->maxLength(255),

            TextInput::make('tiktok')
                ->label('TikTok')
                ->placeholder('@username')
                ->maxLength(255),

            TextInput::make('cmo')
                ->label('CMO / Mediator')
                ->placeholder('Nama CMO')
                ->maxLength(255),

            TextInput::make('cmo_fee')
                ->label('Fee CMO')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),

            Select::make('order_source')
                ->label('Sumber Order')
                ->options([
                    'fb'      => 'Facebook',
                    'ig'      => 'Instagram',
                    'tiktok'  => 'TikTok',
                    'walk_in' => 'Walk In',
                ])
                ->searchable()
                ->placeholder('Pilih sumber order'),

            TextInput::make('branch_name')
                ->label('Cabang')
                ->placeholder('Nama Cabang')
                ->maxLength(255),

            Select::make('result')
                ->label('Hasil')
                ->options([
                    'CASH' => 'CASH',
                    'TT'   => 'TT',
                    'ACC'  => 'ACC',
                    'X'    => 'X',
                ])
                ->searchable(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'proses'  => 'Proses',
                    'kirim'   => 'Kirim',
                    'selesai' => 'Selesai',
                ])
                ->default('proses')
                ->required(),

            Textarea::make('notes')
                ->label(__('tables.note'))
                ->columnSpanFull(),
        ]);
    }
}
