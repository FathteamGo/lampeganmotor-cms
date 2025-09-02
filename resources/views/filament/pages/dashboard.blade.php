<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Section --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Filter Periode
                    </h3>
                </div>
                
                {{ $this->filtersForm }}
            </div>
        </div>

        {{-- Stats Widgets --}}
        <div class="grid gap-6">
            @foreach ($this->getWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>
    </div>

    <style>
        /* Custom styling untuk date picker agar sesuai tema dark */
        .dark .fi-input-wrp {
            background-color: rgb(55, 65, 81) !important;
            border-color: rgb(75, 85, 99) !important;
        }
        
        .dark .fi-input {
            background-color: transparent !important;
            color: white !important;
        }
        
        .dark .fi-fo-field-wrp-label {
            color: rgb(209, 213, 219) !important;
        }

        /* Styling untuk date picker dropdown */
        .dark .flatpickr-calendar {
            background: rgb(31, 41, 55) !important;
            border-color: rgb(75, 85, 99) !important;
        }

        .dark .flatpickr-day {
            color: white !important;
        }

        .dark .flatpickr-day:hover {
            background: rgb(55, 65, 81) !important;
        }

        .dark .flatpickr-day.selected {
            background: rgb(59, 130, 246) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</x-filament-panels::page>