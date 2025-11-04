<?php

namespace App\Filament\Resources\SalesSummaries\Tables;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesSummaryExport;
use Filament\Actions\Action as ActionsAction;

class SalesSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Sales')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sales_count')
                    ->label('Unit Terjual')
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState() ?? [];
                        $query = DB::table('sales')
                            ->where('user_id', $record->id)
                            ->where('status', '!=', 'cancel');

                        if (!empty($filters['month']) && !empty($filters['year'])) {
                            $query->whereMonth('sale_date', $filters['month'])
                                  ->whereYear('sale_date', $filters['year']);
                        }

                        return $query->count();
                    }),

                TextColumn::make('total_omzet')
                    ->label('Total Omzet')
                    ->money('IDR', true)
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState() ?? [];
                        $query = DB::table('sales')
                            ->where('user_id', $record->id)
                            ->where('status', '!=', 'cancel');

                        if (!empty($filters['month']) && !empty($filters['year'])) {
                            $query->whereMonth('sale_date', $filters['month'])
                                  ->whereYear('sale_date', $filters['year']);
                        }

                        return $query->sum('sale_price');
                    }),

                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->money('IDR', true)
                    ->getStateUsing(fn($record) => $record->bonus ?? 0),

                TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', true)
                    ->getStateUsing(fn($record) => $record->base_salary ?? 0),

                TextColumn::make('total_income')
                    ->label('Total Penghasilan')
                    ->money('IDR', true)
                    ->getStateUsing(fn($record) => ($record->base_salary ?? 0) + ($record->bonus ?? 0)),
            ])
            ->filters([
                Filter::make('periode')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
                            ]),
                        Select::make('year')
                            ->label('Tahun')
                            ->options(fn() => DB::table('sales')
                                ->selectRaw('YEAR(sale_date) as year')
                                ->where('status','!=','cancel')
                                ->groupBy('year')
                                ->pluck('year','year')
                                ->toArray()
                            ),
                    ]),
            ])
           ->headerActions([
                ActionsAction::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-s-arrow-down-tray') 
                    ->action(function ($data) {
                        $filters = $data['filters'] ?? [];
                        $month = $filters['periode']['month'] ?? null;
                        $year  = $filters['periode']['year'] ?? null;

                        return Excel::download(new SalesSummaryExport($month, $year), 'sales_summary.xlsx');
                    }),
            ])
            ->defaultSort('name','asc');
    }
}
