{{-- <x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            حفظ التغييرات
        </x-filament::button>
    </form>
</x-filament-panels::page>
 --}}


<div class="py-6">
    <div class="flex max-w-sm mx-auto overflow-hidden bg-white rounded-lg shadow-lg lg:max-w-4xl">

        <div class="w-full p-8 lg:w-full">
            <form wire:submit.prevent="submit" class="space-y-6">

                <div class="flex items-center justify-between mt-4">
                    <span class="w-1/5 border-b lg:w-1/4"></span>
                    <a href="#" class="text-xs text-center text-gray-500 uppercase">Edit Information</a>
                    <span class="w-1/5 border-b lg:w-1/4"></span>
                </div>

                <div class="mt-4">
                    {{ $this->form }}
                </div>
                <div class="mt-8">

                    <x-filament::button type="submit" color='success'
                        class="w-full px-4 py-2 font-bold rounded hover:bg-gray-600">
                        حفظ التغييرات
                    </x-filament::button>
                </div>
            </form>

        </div>
    </div>
</div>
