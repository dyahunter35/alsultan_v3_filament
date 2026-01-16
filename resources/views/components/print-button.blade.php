<div class="fixed bottom-6 left-6 no-print flex flex-col gap-3">
    {{-- زر العودة للأعلى --}}
    <x-filament::button color="gray" icon="heroicon-o-chevron-double-up"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" title="العودة للأعلى">
    </x-filament::button>

    {{-- زر التمرير للأسفل (الذي فعلناه سابقاً) --}}
    <x-filament::button color="gray" icon="heroicon-o-chevron-double-down"
        onclick="window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })">
    </x-filament::button>

    {{-- زر الطباعة --}}
    <x-filament::button color="info" icon="heroicon-o-printer" onclick="window.print()">
        طباعة
    </x-filament::button>

    <div x-data="{}" @keydown.window.ctrl.h="updateQty" class="relative"></div>
</div>