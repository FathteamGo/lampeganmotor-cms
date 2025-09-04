<?php

namespace App\Filament\Resources\Requests;

use App\Filament\Resources\Requests\Pages\CreateRequest;
use App\Filament\Resources\Requests\Pages\EditRequest;
use App\Filament\Resources\Requests\Pages\ListRequests;
use App\Filament\Resources\Requests\Pages\ViewRequest;
use App\Filament\Resources\Requests\Schemas\RequestForm;
use App\Filament\Resources\Requests\Tables\RequestsTable;
use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $recordTitleAttribute = 'title'; // sesuaikan field judul utama

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.transactions');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.requests');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.requests');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.requests');
    }

    /** 🔹 Urutan Sidebar */
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

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
            'index'  => ListRequests::route('/'),
            'create' => CreateRequest::route('/create'),
            'view'   => ViewRequest::route('/{record}'),
            // kalau butuh edit:
            // 'edit'   => EditRequest::route('/{record}/edit'),
        ];
    }
}
