@php
    $documents = $documents ?? [];
@endphp

<div x-data="{ 
    search: '',
    {{-- دالة بسيطة للتحقق مما إذا كان المستند يطابق البحث --}}
    filterDoc(name, type) {
        if (this.search === '') return true;
        const term = this.search.toLowerCase();
        return name.toLowerCase().includes(term) || type.toLowerCase().includes(term);
    }
}" class="space-y-6">

    {{-- شريط البحث العلوي --}}
    <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 pb-4 border-b border-gray-100 dark:border-gray-800">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-filament::icon icon="heroicon-m-magnifying-glass" class="w-5 h-5 text-gray-400" />
            </div>
            <x-filament-forms::text-input 
                x-model="search" 
                type="text" 
                placeholder="ابحث باسم المستند أو النوع (مثلاً: رخصة، تأمين...)" 
                class="block w-full ps-10 p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
        ></x-filament-forms::text-input>
        </div>
    </div>

    {{-- شبكة المستندات --}}
    <div class="space-y-8 p-2">
        @forelse($documents as $doc)
            <div 
                x-show="filterDoc('{{ addslashes($doc->name) }}', '{{ addslashes($doc->type ?? '') }}')"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="border-b border-gray-100 dark:border-gray-800 pb-4"
            >
                {{-- عنوان المستند --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $doc->name }}</h3>
                        <span class="text-xs text-gray-500 italic">
                            {{ $doc->type ?? 'مستند عام' }} | {{ $doc->issuance_date?->format('Y-m-d') }}
                        </span>
                    </div>
                    <div class="px-3 py-1 bg-primary-50 dark:bg-primary-400/10 text-primary-600 rounded-full text-xs font-medium">
                        {{ $doc->media->count() }} ملفات
                    </div>
                </div>

                {{-- المرفقات --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($doc->media as $file)
                        {{-- كود عرض الملف (نفس الكود السابق الذي صممناه) --}}
                        <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg group hover:border-primary-500 transition-colors">
                            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded bg-white dark:bg-gray-800 shadow-sm">
                                @if(str_contains($file->mime_type, 'image'))
                                    <img src="{{ $file->getUrl() }}" class="w-10 h-10 object-cover rounded">
                                @else
                                    <x-filament::icon 
                                        :icon="str_contains($file->mime_type, 'pdf') ? 'heroicon-o-document-text' : 'heroicon-o-document'" 
                                        class="w-6 h-6 {{ str_contains($file->mime_type, 'pdf') ? 'text-danger-500' : 'text-primary-500' }}" 
                                    />
                                @endif
                            </div>
                            <div class="ms-3 flex-1 min-w-0">
                                <p class="text-xs font-medium truncate text-gray-700 dark:text-gray-300">{{ $file->file_name }}</p>
                                <p class="text-[10px] text-gray-500 uppercase">{{ $file->human_readable_size }}</p>
                            </div>
                            <div class="flex space-x-1 rtl:space-x-reverse">
                                <a href="{{ $file->getUrl() }}" target="_blank" class="p-1 text-gray-400 hover:text-primary-500">
                                    <x-filament::icon icon="heroicon-m-eye" class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <p>لا توجد مستندات مسجلة</p>
            </div>
        @endforelse

        {{-- رسالة تظهر عند عدم وجود نتائج للبحث --}}
        <div 
            x-show="search !== '' && $el.parentElement.querySelectorAll('[x-show*=\'filterDoc\']:not([style*=\'display: none\'])').length === 0" 
            class="text-center py-10 text-gray-500"
            style="display: none;"
        >
            لا توجد نتائج مطابقة لبحثك..
        </div>
    </div>
</div>