<x-filament::page class="print:p-0">
    @php
        $contract = $this->record;
        $isRtl = app()->getLocale() === 'ar';
    @endphp

    <div class="max-w-5xl mx-auto overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl print:shadow-none print:border-none"
        id="report-content">

        <!-- Decoration Top Bar -->
        <div class="w-full h-2 bg-primary-600"></div>

        <div class="p-8 md:p-12">
            <!-- Header Section -->
            <div
                class="flex flex-row items-start justify-between pb-8 mb-10 border-b border-gray-100 md:flex-row md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $contract->company->name }}</h1>
                    <h2 class="mt-2 text-xl font-medium text-primary-600">{{ $contract->title }}</h2>
                </div>
                <div class="mt-4 md:mt-0 text-left {{ $isRtl ? 'md:text-left' : 'md:text-right' }}">
                    <span
                        class="block text-sm tracking-wider text-gray-500 uppercase">{{ __('contract.reference_no') }}</span>
                    <span class="block px-3 py-1 mt-1 font-mono text-lg font-bold text-gray-800 rounded bg-gray-50">
                        #{{ $contract->reference_no }}
                    </span>
                </div>
            </div>

            <!-- Contract Info Grid -->
            <div class="grid grid-cols-3 gap-6 p-6 mb-10 border border-gray-100 rounded-lg md:grid-cols-3 bg-gray-50">
                <div>
                    <span
                        class="block mb-1 text-xs font-semibold text-gray-500 uppercase">{{ __('contract.fields.effective_date.label') }}</span>
                    <span class="font-medium text-gray-900">{{ $contract->effective_date?->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span
                        class="block mb-1 text-xs font-semibold text-gray-500 uppercase">{{ __('contract.fields.duration_months.label') }}</span>
                    <span class="font-medium text-gray-900">{{ $contract->duration_months }}
                        {{ __('contract.fields.duration_months.unit') }}</span>
                </div>
                <div>
                    <span
                        class="block mb-1 text-xs font-semibold text-gray-500 uppercase">{{ __('contract.fields.total_amount.label') }}</span>
                    <span
                        class="text-lg font-bold text-green-600">${{ number_format($contract->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Scope of Services -->
            <div class="mb-10">
                <h3 class="flex items-center mb-4 text-lg font-bold text-gray-900">
                    <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-primary-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    {{ __('contract.fields.scope_of_services.label') }}
                </h3>
                <div class="p-4 leading-relaxed text-gray-600 bg-white border border-gray-100 rounded-lg shadow-sm">
                    {{ $contract->scope_of_services }}
                </div>
            </div>

            <!-- Contract Items Table -->
            @if ($contract->items->count())
                <div class="mb-10">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">{{ __('contract.sections.items.label') }}</h3>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-center text-sm font-semibold text-gray-900 sm:pl-6">
                                        {{ __('contract.fields.items.fields.description.label') }}</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        {{ __('contract.fields.items.fields.quantity.label') }}</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        {{ __('contract.fields.items.fields.unit_price.label') }}</th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        {{ __('contract.fields.items.fields.total_price.label') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-center bg-white divide-y divide-gray-200">
                                @foreach ($contract->items as $item)
                                    <tr>
                                        <td
                                            class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6">
                                            {{ $item->description }}
                                            <div class="text-xs text-gray-500 font-normal mt-0.5">
                                                {{ $item->size }} | {{ $item->weight }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $item->quantity }}</td>
                                        <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $item->unit_price }}</td>
                                        <td class="px-3 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                            {{ $item->total_price }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Documents Section (Redesigned) -->
            @if ($contract->documents->count())
                <div class="mb-10 print:hidden">
                    <h3 class="flex items-center mb-6 text-lg font-bold text-gray-900">
                        <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-primary-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                            </path>
                        </svg>
                        {{ __('contract.sections.documents.label') }}
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($contract->documents as $doc)
                            @php
                                $media = $doc->media()->where('collection_name', 'contract_docs')->get();

                            @endphp
                            <div class="mb-10">
                                <h3 class="flex items-center mb-4 text-lg font-bold text-gray-900">
                                    <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-primary-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>
                                    {{ $doc->name }}
                                </h3>

                                @foreach ($media as $m)
                                    @php
                                        $mime = $m->mime_type ?? 'default';

                                        // Dynamic Styling based on Mime Type
                                        $icon = 'heroicon-o-document';
                                        $colorClass = 'bg-gray-50 text-gray-600 border-gray-200';

                                        if (str_contains($mime, 'pdf')) {
                                            $icon = 'heroicon-o-document-text';
                                            $colorClass = 'bg-red-50 text-red-600 border-red-100 hover:border-red-300';
                                        } elseif (str_contains($mime, 'image')) {
                                            $icon = 'heroicon-o-photo';
                                            $colorClass =
                                                'bg-blue-50 text-blue-600 border-blue-100 hover:border-blue-300';
                                        } elseif (
                                            str_contains($mime, 'spreadsheet') ||
                                            str_contains($mime, 'excel') ||
                                            str_contains($mime, 'csv')
                                        ) {
                                            $icon = 'heroicon-o-table-cells';
                                            $colorClass =
                                                'bg-green-50 text-green-600 border-green-100 hover:border-green-300';
                                        } elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) {
                                            $icon = 'heroicon-o-document-text';
                                            $colorClass =
                                                'bg-blue-50 text-blue-800 border-blue-200 hover:border-blue-400';
                                        }
                                    @endphp
                                    <a href="{{ $m->original_url }}" target="_blank"
                                        class="group relative flex items-center p-4 border rounded-xl transition-all duration-200 hover:shadow-md {{ $colorClass }}">
                                        <div class="flex-shrink-0">
                                            <x-filament::icon :icon="$icon"
                                                class="w-10 h-10 transition-transform duration-200 opacity-80 group-hover:scale-110" />
                                        </div>
                                        <div class="{{ $isRtl ? 'mr-4' : 'ml-4' }} flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $doc->title ?? basename($m->file_name) }}
                                            </p>
                                            <p class="flex items-center mt-1 text-xs opacity-70">
                                                <span
                                                    class="font-bold tracking-wider uppercase">{{ Str::afterLast($m->mime_type, '/') }}</span>
                                                <span class="mx-1">•</span>
                                                <span>{{ $doc->issuance_date?->format('d M Y') }}</span>
                                            </p>
                                        </div>
                                        <div
                                            class="absolute top-4 {{ $isRtl ? 'left-4' : 'right-4' }} opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                </path>
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Clauses Section -->
            <div class="p-6 mb-12 border border-gray-200 rounded-lg bg-gray-50 break-inside-avoid">
                <h3 class="mb-4 text-lg font-bold text-gray-900">{{ __('contract.sections.clauses.label') }}</h3>
                <div class="space-y-4 text-sm text-gray-700">
                    <div>
                        <strong
                            class="block mb-1 text-gray-900">{{ __('contract.fields.confidentiality_clause.label') }}</strong>
                        <p>{{ $contract->confidentiality_clause }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <strong
                            class="block mb-1 text-gray-900">{{ __('contract.fields.termination_clause.label') }}</strong>
                        <p>{{ $contract->termination_clause }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <strong
                            class="block mb-1 text-gray-900">{{ __('contract.fields.governing_law.label') }}</strong>
                        <p>{{ $contract->governing_law }}</p>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="grid grid-cols-2 gap-12 mt-16 md:grid-cols-2 break-inside-avoid">
                <div class="text-center">
                    <p class="mb-16 text-sm font-semibold text-gray-500 uppercase">
                        {{ __('contract.signature.authorized') }}</p>
                    <div class="w-3/4 pt-2 mx-auto border-t-2 border-gray-300 border-dashed">
                        <p class="font-bold text-gray-900">{{ $contract->company->name }}</p>
                        <p class="mt-1 text-xs text-gray-400">Date: ________________</p>
                    </div>
                </div>
                <div class="text-center">
                    <p class="mb-16 text-sm font-semibold text-gray-500 uppercase">
                        {{ __('contract.signature.provider') }}</p>
                    <div class="w-3/4 pt-2 mx-auto border-t-2 border-gray-300 border-dashed">
                        <p class="font-bold text-gray-900">{{ __('contract.signature.placeholder') }}</p>
                        <p class="mt-1 text-xs text-gray-400">Date: ________________</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament::page>
