@php
    $documents = $documents ?? [];
    // جلب السنوات المتوفرة فقط من المستندات
    $years = $documents->map(fn($doc) => $doc->issuance_date?->format('Y'))->filter()->unique()->sortDesc();
    
    // مصفوفة الشهور العربية
    $months = [
        '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
        '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
        '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر',
    ];
@endphp

<div x-data="{ 
    search: '',
    selectedYear: '',
    selectedMonth: '',
    {{-- دالة الفلترة الشاملة --}}
    filterDoc(name, type, date) {
        if (!date) return this.search === '' && this.selectedYear === '' && this.selectedMonth === '';
        
        const matchesSearch = this.search === '' || 
                            name.toLowerCase().includes(this.search.toLowerCase()) || 
                            type.toLowerCase().includes(this.search.toLowerCase());
        
        const matchesYear = this.selectedYear === '' || date.startsWith(this.selectedYear);
        
        // التحقق من الشهر (بافتراض أن التاريخ بصيغة Y-m-d)
        const docMonth = date.split('-')[1]; 
        const matchesMonth = this.selectedMonth === '' || docMonth === this.selectedMonth;
        
        return matchesSearch && matchesYear && matchesMonth;
    },
    resetFilters() {
        this.search = '';
        this.selectedYear = '';
        this.selectedMonth = '';
    }
}" class="space-y-6">

    {{-- شريط الفلاتر --}}
    <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 pb-4 border-b border-gray-100 dark:border-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            {{-- البحث النصي --}}
            <div class="md:col-span-2">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input x-model="search" type="text" placeholder="ابحث بالاسم أو النوع..." />
                </x-filament::input.wrapper>
            </div>

            {{-- فلتر السنة --}}
            <div>
                <x-filament::input.wrapper prefix-icon="heroicon-m-calendar">
                    <x-filament::input.select x-model="selectedYear">
                        <option value="">كل السنوات</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- فلتر الشهر --}}
            <div class="relative flex items-center gap-2">
                <x-filament::input.wrapper prefix-icon="heroicon-m-calendar-days" class="flex-1">
                    <x-filament::input.select x-model="selectedMonth">
                        <option value="">كل الشهور</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                
                {{-- زر إعادة الضبط --}}
                <x-filament::icon-button 
                    icon="heroicon-m-x-mark" 
                    color="gray" 
                    label="مسح"
                    x-show="search !== '' || selectedYear !== '' || selectedMonth !== ''"
                    x-on:click="resetFilters()"
                />
            </div>
        </div>
    </div>

    {{-- عرض النتائج --}}
    <div class="space-y-6">
        @forelse($documents as $doc)
            <div x-show="filterDoc('{{ addslashes($doc->name) }}', '{{ addslashes($doc->type ?? '') }}', '{{ $doc->issuance_date?->format('Y-m-d') }}')"
                x-transition class="p-4 border border-gray-100 dark:border-gray-800 rounded-xl bg-white dark:bg-gray-900/50 shadow-sm">
                
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">{{ $doc->name }}</h4>
                        <div class="flex gap-2 mt-1">
                            <x-filament::badge color="gray" size="sm">{{ $doc->type ?? 'عام' }}</x-filament::badge>
                            <x-filament::badge color="primary" icon="heroicon-m-calendar" size="sm">
                                {{ $doc->issuance_date?->format('d M, Y') }}
                            </x-filament::badge>
                        </div>
                    </div>
                </div>

                {{-- شبكة المرفقات --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($doc->media as $file)
                        <a href="{{ $file->getUrl() }}" target="_blank" class="group relative block aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 border dark:border-gray-700">
                            @if(str_contains($file->mime_type, 'image'))
                                <img src="{{ $file->getUrl() }}" class="w-full h-full object-cover transition group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-2">
                                    <x-filament::icon 
                                        :icon="str_contains($file->mime_type, 'pdf') ? 'heroicon-o-document-text' : 'heroicon-o-document'" 
                                        class="w-8 h-8 mb-1 {{ str_contains($file->mime_type, 'pdf') ? 'text-danger-500' : 'text-primary-500' }}" 
                                    />
                                    <span class="text-[10px] text-gray-500 truncate w-full text-center">{{ $file->file_name }}</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold">
                                معاينة
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20 text-gray-400">لا توجد بيانات</div>
        @endforelse

        {{-- رسالة "لم يتم العثور على نتائج" --}}
        <div x-cloak x-show="[...$el.parentElement.children].filter(el => el.style.display !== 'none').length === 0" 
             class="text-center py-12">
            <x-filament::icon icon="heroicon-o-face-frown" class="w-12 h-12 mx-auto text-gray-300 mb-2" />
            <p class="text-gray-500 italic">لا توجد مستندات تطابق اختياراتك في هذا الشهر/السنة..</p>
        </div>
    </div>
</div>