@props(['label' => null])

{{-- ملاحظة: وسم head يفضل أن يكون في الـ Layout الأساسي، ولكن سأبقي الفكرة هنا مع تحسينها --}}
@isset($label)
@section('title', $label)
@endisset

<header class="mb-8 container mx-auto">
    <div class="flex flex-col items-center justify-center text-center space-y-2 mb-6">
        <div class="p-2">
            <img src="{{ asset('asset/logo.png') }}" alt="logo" width="90" />
        </div>

        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">
                {{ __('app.name') }}
            </h1>
            <p class="text-slate-500 font-medium flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ __('app.address') }}
            </p>
        </div>
    </div>

    <div
        class="relative overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 p-5 transition-all">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">

            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    {{-- <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">نوع
                        التقرير</span> --}}
                    <h4 class="text-lg font-bold text-slate-700 leading-tight">
                        {{ $label ?? 'تقرير عام' }}
                    </h4>
                </div>
            </div>

            <div class="flex items-center gap-3 text-right">
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">تاريخ الإصدار</span>
                    <time class="text-lg font-bold text-slate-700 tabular-nums">
                        {{ now()->format('Y/m/d') }}
                    </time>
                </div>
                <div class="bg-slate-200 p-2 rounded-lg text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

        </div>
    </div>
</header>