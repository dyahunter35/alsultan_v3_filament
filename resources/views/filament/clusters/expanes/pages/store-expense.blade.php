<x-filament-panels::page>
    {{-- <form wire:submit.prevent="{{ $editingRecord ? 'update' : 'create' }}">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-plus" class="mt-4">
            {{ $editingRecord ? 'تحديث المصروف' : 'إضافة مصروف جديد' }}
        </x-filament::button>
    </form> --}}

    <x-filament-actions::modals />

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
