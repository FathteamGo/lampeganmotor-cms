<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Actions;
use Filament\Facades\Filament;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;
use App\Models\Brand;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    /**
     * Header actions (View + Delete hanya untuk owner)
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }

    /**
     * Tangani proses update agar bisa munculin notif kalau ada duplikat
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten();

            if ($messages->some(fn($msg) => str_contains(strtolower($msg), 'unique'))) {
                Notification::make()
                    ->danger()
                    ->title('Data Duplikat')
                    ->body('Data kendaraan dengan field unik (VIN, No Mesin, No Plat, atau BPKB) sudah terdaftar.')
                    ->send();
            }

            throw $e;
        }
    }

    /**
     * Auto-fill form data dari relasi kendaraan
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $vehicleModel = VehicleModel::find($this->record->vehicle_model_id);
        $type = Type::find($this->record->type_id);
        $color = Color::find($this->record->color_id);
        $year = Year::find($this->record->year_id);
        $brand = Brand::find(optional($vehicleModel)->brand_id);

        $data['brand_name'] = $brand?->name;
        $data['vehicle_model_name'] = $vehicleModel?->name;
        $data['type_name'] = $type?->name;
        $data['color_name'] = $color?->name;
        $data['year_name'] = $year?->year;

        return $data;
    }
}
