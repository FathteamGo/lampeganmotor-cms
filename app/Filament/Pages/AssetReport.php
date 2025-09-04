<?php

namespace App\Filament\Pages;

use App\Models\OtherAsset;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class AssetReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;


    protected static string|\UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.asset_report';

    protected static ?string $title = 'navigation.asset_report_title';


    protected string $view = 'filament.pages.asset-report';

   public static function shouldRegisterNavigation(): bool
{
    $user = Auth::user();

    return $user && $user->role === 'owner';
}

 public static function canAccess(): bool
    {
    $user = Auth::user();

    return $user && $user->role === 'owner';
    }

    
    public static function getNavigationGroup(): ?string
    {
        return __(static::$navigationGroup);
    }

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel);
    }

    public function getTitle(): string
    {
        return __(static::$title);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OtherAsset::query())
            ->heading('Harta Tidak Bergerak')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('navigation.other_assets'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('tables.description'))
                    ->wrap(),

                Tables\Columns\TextColumn::make('acquisition_date')
                    ->label(__('navigation.acquisition_date'))
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('navigation.asset_value'))
                    ->money('idr', true)
                    ->alignRight(),
            ])
            ->filters([
                Filter::make('acquisition_date')
                    ->form([
                        DatePicker::make('from')->label(__('navigation.from_date')),
                        DatePicker::make('until')->label(__('navigation.until_date')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('acquisition_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('acquisition_date', '<=', $date));
                    }),
            ]);
    }
}

