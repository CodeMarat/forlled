<x-filament::tabs>
    <x-filament::tabs.item
        :active="request()->is('admin/represented-countries*')"
        :href="\App\Filament\Resources\RepresentedCountries\RepresentedCountryResource::getUrl('index')"
        tag="a"
    >
        Worldwide countries
    </x-filament::tabs.item>
    <x-filament::tabs.item
        :active="request()->is('admin/locations*')"
        :href="\App\Filament\Resources\LocationResource::getUrl('index')"
        tag="a"
    >
        USA salons
    </x-filament::tabs.item>
</x-filament::tabs>
