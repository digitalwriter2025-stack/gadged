<x-filament-panels::page>
    <form wire:submit.prevent="save" class="gx-settings">
        {{ $this->form }}

        <div class="gx-settings-savebar">
            <div class="gx-settings-savebar-text">
                <x-filament::icon icon="heroicon-o-information-circle" class="gx-settings-savebar-icon" />
                <span>Changes apply to the storefront as soon as you save. The page cache is refreshed automatically.</span>
            </div>

            <x-filament::button type="submit" size="lg" icon="heroicon-o-check" color="primary" wire:target="save">
                Save All Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
