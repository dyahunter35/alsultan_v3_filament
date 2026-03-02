@php
    $media = isset($media) ? $media : $getState();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($media as $file)
        @php
            $isPdf = $file->mime_type === 'application/pdf';
            $isImage = str_contains($file->mime_type, 'image');
            $icon = match (true) {
                $isPdf => 'heroicon-o-document-text',
                $isImage => 'heroicon-o-photo',
                default => 'heroicon-o-document'
            };
            $colorClass = match (true) {
                $isPdf => 'text-danger-600 bg-danger-50 dark:bg-danger-400/10',
                $isImage => 'text-success-600 bg-success-50 dark:bg-success-400/10',
                default => 'text-primary-600 bg-primary-50 dark:bg-primary-400/10'
            };
        @endphp

        <div
            class="flex items-center p-3 space-x-4 rtl:space-x-reverse bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
            {{-- الأيقونة أو المعاينة --}}
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg {{ $colorClass }}">
                @if($isImage)
                    <img src="{{ $file->getUrl() }}" class="w-12 h-12 object-cover rounded-lg shadow-inner">
                @else
                    <x-filament::icon :icon="$icon" class="w-7 h-7" />
                @endif
            </div>

            {{-- تفاصيل الملف --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" title="{{ $file->name }}">
                    {{ $file->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $file->human_readable_size }} | {{ strtoupper($file->extension) }}
                </p>
            </div>

            {{-- أزرار التحكم --}}
            <div class="flex items-center space-x-1 rtl:space-x-reverse">
                <a href="{{ $file->getUrl() }}" target="_blank"
                    class="p-2 text-gray-400 hover:text-primary-600 transition-colors" title="فتح المستند">
                    <x-filament::icon icon="heroicon-m-eye" class="w-5 h-5" />
                </a>
                <a href="{{ $file->getUrl() }}" download class="p-2 text-gray-400 hover:text-success-600 transition-colors"
                    title="تحميل">
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="w-5 h-5" />
                </a>
            </div>
        </div>
    @endforeach
</div>

@if(count($media) === 0)
    <div class="text-center p-4 text-gray-400 text-sm italic">
        لا توجد مستندات مرفقة حالياً
    </div>
@endif