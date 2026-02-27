<div class="fixed bottom-6 left-6 no-print flex flex-col gap-3 z-50">
    {{-- زر العودة للأعلى --}}
    <x-filament::button color="gray" icon="heroicon-o-chevron-double-up" {{-- ملاحظة: Filament يقبل المصفوفة أو النص
        للاختصارات --}} :key-bindings="['command+up', 'ctrl+shift+up']"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" x-tooltip="{
            content: 'العودة للأعلى',
            placement: 'right',
        }">
    </x-filament::button>

    {{-- زر التمرير للأسفل --}}
    <x-filament::button color="gray" icon="heroicon-o-chevron-double-down"
        :key-bindings="['command+down', 'ctrl+shift+down']"
        onclick="window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })" x-tooltip="{
            content: 'إلى أسفل التقرير (ctrl+shift+down)',
            placement: 'right',
        }">
    </x-filament::button>

    {{-- زر الطباعة --}}
    <x-filament::button color="success" icon="heroicon-o-printer" :key-bindings="['command+p', 'ctrl+p']"
        onclick="printWithHeader2('report-content','{{ $this->getPrintTitle() }}')" x-tooltip="{
            content: 'طباعة التقرير (Ctrl+P)',
            placement: 'right',
        }">
    </x-filament::button>

</div>