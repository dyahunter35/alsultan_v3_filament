<x-filament-panels::page>

    <form wire:submit="create">
        {{ $this->form }}

        <x-filament::button icon="heroicon-m-plus" type='submit'>
            Submit
        </x-filament::button>
    </form>
    <x-filament-actions::modals />

    {{ $this->table }}
</x-filament-panels::page>
