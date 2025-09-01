<?php

namespace App\Filament\Pages;

use App\Models\Request as VehicleRequest;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Navigation\NavigationGroup;
use UnitEnum;

class RequestPage extends Page implements HasTable
{
    use InteractsWithTable;


    // Harus NON-static dan non-nullable string (samakan dengan parent)
    protected string $view = 'filament.pages.requests';

    /* ---------- Navigation (pakai getter biar aman tipe) ---------- */

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Transactions';
    }

    public static function getNavigationLabel(): string
    {
        return 'Requests';
    }

    public static function getNavigationIcon(): \BackedEnum|string|null
    {
        return 'heroicon-o-clipboard-document';
    }

    public static function getNavigationSort(): ?int
    {
        return 3; // muncul di bawah Sales jika Sales sort-nya < 3
    }

    /* ---------- Title (non-static method, hindari property) ---------- */

    public function getTitle(): string
    {
        return 'Requests';
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->emptyStateHeading('No requests yet')
            ->emptyStateDescription('Belum ada data request.')
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return VehicleRequest::query()
            ->with(['supplier', 'brand', 'vehicleModel', 'year', 'photos'])
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            // Kolom pertama: Tahun
            TextColumn::make('year.year')
                ->label('Tahun')
                ->sortable()
                ->searchable(),

            TextColumn::make('supplier.name')
                ->label('Nama')
                ->sortable()
                ->searchable(),

            TextColumn::make('supplier.phone')
                ->label('WhatsApp')
                ->sortable()
                ->searchable(),

            TextColumn::make('brand.name')
                ->label('Merk')
                ->sortable()
                ->searchable(),

            TextColumn::make('vehicleModel.name')
                ->label('Model')
                ->sortable()
                ->searchable(),

            TextColumn::make('odometer')
                ->label('Odometer (KM)')
                ->sortable(),

            // Foto pertama request
            ImageColumn::make('photos.0.path')
                ->label('Foto')
                ->disk('public')
                ->square(),

            TextColumn::make('notes')
                ->label('Catatan')
                ->limit(40)
                ->tooltip(fn ($state) => $state),

            SelectColumn::make('status')
                ->label('Status')
                ->options([
                    'hold'   => 'Hold',
                    'avaiable' => 'Avaiable',
                    'in_repair' => 'In_Repair',
                    'sold'    => 'Sold',
                ])
                ->selectablePlaceholder(false)
                ->rules(['required']),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'hold'   => 'Hold',
                    'avaiable' => 'Avaiable',
                    'in_repair' => 'In_Repair',
                    'sold'    => 'Sold',
                ])
                ->default('hold'),

            SelectFilter::make('brand_id')
                ->label('Merk')
                ->relationship('brand', 'name'),

            SelectFilter::make('year_id')
                ->label('Tahun')
                ->relationship('year', 'year'),
        ];
    }
}
