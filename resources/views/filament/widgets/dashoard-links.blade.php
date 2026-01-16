<x-filament-widgets::widget>
    <x-filament::section shadow>
        {{--    
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon 
                    icon="heroicon-m-bolt" 
                    class="w-5 h-5 text-warning-500" 
                />
                <span class="text-base font-bold">الوصول السريع للمهام</span>
            </div>
        </x-slot>
        --}}  

        <div class="py-2">
            {{-- شبكة البطاقات --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach($this->getActions() as $action)
                    <x-action-tile 
                        :title="$action['title']" 
                        :icon="$action['icon']" 
                        :color="$action['color']"
                        :url="$action['url']" 
                        :value="$action['value'] ?? null" 
                    />
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>