@props([
    'title',
    'icon',
    'value' => null,
    'url' => '#',
    'color' => 'primary',
])

@php
    $colors = [
        'primary' => 'text-primary-600 bg-primary-50 border-primary-100 hover:bg-primary-100 hover:border-primary-300',
        'success' => 'text-success-600 bg-success-50 border-success-100 hover:bg-success-100 hover:border-success-300',
        'warning' => 'text-warning-600 bg-warning-50 border-warning-100 hover:bg-warning-100 hover:border-warning-300',
        'danger'  => 'text-danger-600 bg-danger-50 border-danger-100 hover:bg-danger-100 hover:border-danger-300',
        'info'    => 'text-info-600 bg-info-50 border-info-100 hover:bg-info-100 hover:border-info-300',
        'gray'    => 'text-gray-600 bg-gray-50 border-gray-100 hover:bg-gray-100 hover:border-gray-300',
    ];
    $theme = $colors[$color] ?? $colors['primary'];
@endphp

<a href="{{ $url }}" 
   {{-- إضافة Alpine.js للتحكم في العداد --}}
   x-data="{ 
        current: 0, 
        target: {{ $value ?? 0 }},
        time: 1500,
        start() {
            let start = null;
            const step = (timestamp) => {
                if (!start) start = timestamp;
                const progress = Math.min((timestamp - start) / this.time, 1);
                this.current = Math.floor(progress * this.target);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
   }"
   x-init="start()"
   class="group relative flex flex-row-reverse items-center justify-between p-4 transition-all duration-300 border rounded-xl shadow-sm {{ $theme }} hover:shadow-lg hover:-translate-y-1">
    
    {{-- منطقة الأيقونة مع أنيميشن دوران بسيط عند الهوفر --}}
    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-white shadow-sm shrink-0 transition-transform duration-500 group-hover:rotate-[12deg]">
        <x-filament::icon 
            :icon="$icon" 
            class="w-7 h-7 transition-transform duration-300 group-hover:scale-110" 
        />
    </div>

    {{-- منطقة النصوص --}}
    <div class="flex flex-col items-start gap-1 overflow-hidden ml-4">
        @if($value !== null)
            {{-- الرقم المتحرك --}}
            <span class="text-3xl font-black leading-none tabular-nums tracking-tight" x-text="current">
                0
            </span>
        @endif
        <span class="text-sm font-bold leading-tight truncate w-full opacity-90 group-hover:opacity-100">
            {{ $title }}
        </span>
    </div>

    {{-- الخط الجانبي التفاعلي --}}
    <div class="absolute right-0 top-1/4 bottom-1/4 w-1.5 rounded-l-full bg-current opacity-20 group-hover:opacity-100 transition-all duration-300 group-hover:h-1/2 group-hover:top-1/4"></div>
</a>