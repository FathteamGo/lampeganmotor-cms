<x-filament-panels::page>
    <a href="{{ route('purchase-report.export', [
        'from' => request('from'),
        'until' => request('until')
    ]) }}" 
   class="filament-button filament-button-size-md filament-button-color-primary">
    Export Excel
</a>
    
    {{$this->table}}
</x-filament-panels::page>
