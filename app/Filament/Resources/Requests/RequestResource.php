<?php

namespace App\Filament\Resources\Requests;

use App\Filament\Resources\Requests\Pages\ListRequests;
use App\Filament\Resources\Requests\Tables\RequestsTable;
use App\Models\Request as VehicleRequest;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Resources\Requests\Schemas\RequestForm;
use App\Filament\Resources\Requests\Pages\CreateRequest;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use App\Models\Request;
use UnitEnum;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;
    protected static string|UnitEnum|null $navigationGroup = 'Transactions';
    protected static ?string $navigationLabel = 'Requests';
    protected static ?int $navigationSort = 3;
 //   protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;
    public static function table(Table $table): Table
    {
        return RequestsTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return RequestForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => CreateRequest::route('/create'),
            'view'  => Pages\ViewRequest::route('/{record}'),
        ];
    }
}
