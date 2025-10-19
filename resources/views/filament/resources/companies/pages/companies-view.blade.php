<x-filament-panels::page>
    <div>
        <div id="report-content">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">تفاصيل الشركة</h2>

                <x-filament::button color="gray" size="sm" onclick="window.print()">
                    <x-heroicon-o-printer class="w-4 h-4 mr-2" />
                    طباعة
                </x-filament::button>
            </div>

            {{ $this->infolist }}
        </div>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #report-content,
                #report-content * {
                    visibility: visible;
                }

                #report-content {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    border: none;
                    box-shadow: none;
                }
            }
        </style>
    </div>

</x-filament-panels::page>
