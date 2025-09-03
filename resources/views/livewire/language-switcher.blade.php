<div> <!-- Root tunggal -->
    <div class="flex items-center space-x-2">
        <div class="relative">
            <x-filament::dropdown>
                <x-slot name="trigger">
                    <x-filament::button
                        color="gray"
                        size="sm"
                        outlined
                        icon="heroicon-o-language"
                        class="flex items-center space-x-1"
                    >
                        <span class="text-xs font-medium">
                            {{ $currentLocale === 'id' ? 'ID' : 'EN' }}
                        </span>
                    </x-filament::button>
                </x-slot>

                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item
                        tag="button"
                        wire:click="switchLanguage('id')"
                        :active="$currentLocale === 'id'"
                        class="cursor-pointer"
                    >
                        <div class="flex items-center space-x-2">
                            <span class="text-lg">🇮🇩</span>
                            <span>Bahasa Indonesia</span>
                        </div>
                    </x-filament::dropdown.list.item>

                    <x-filament::dropdown.list.item
                        tag="button"
                        wire:click="switchLanguage('en')"
                        :active="$currentLocale === 'en'"
                        class="cursor-pointer"
                    >
                        <div class="flex items-center space-x-2">
                            <span class="text-lg">🇺🇸</span>
                            <span>English</span>
                        </div>
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>
    </div>
</div>
