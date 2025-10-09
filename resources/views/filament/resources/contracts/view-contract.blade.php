<x-filament::page class="p-8" :class="app()->getLocale() === 'ar' ? 'rtl' : 'ltr'">
    @php
        $contract = $this->record;
    @endphp

    <div class="p-6 bg-white rounded-lg shadow-lg" id="report-content">
        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold">{{ $contract->company->name }}</h1>
            <h2 class="mt-2 text-xl">{{ $contract->title }}</h2>
            <p class="mt-1 text-gray-500">{{ __('contract.reference_no') }}: {{ $contract->reference_no }}</p>
        </div>

        <!-- Contract Info -->
        <div class="mb-4">
            <h3 class="mb-2 font-semibold">{{ __('contract.sections.contract_info.label') }}</h3>
            <ul class="text-gray-700 list-disc list-inside">
                <li><strong>{{ __('contract.fields.effective_date.label') }}:</strong>
                    {{ $contract->effective_date?->format('Y-m-d') }}</li>
                <li><strong>{{ __('contract.fields.duration_months.label') }}:</strong> {{ $contract->duration_months }}
                    {{ __('contract.fields.duration_months.unit') }}</li>
                <li><strong>{{ __('contract.fields.total_amount.label') }}:</strong>
                    ${{ number_format($contract->total_amount, 2) }}</li>
            </ul>
        </div>

        <!-- Scope of Services -->
        <div class="mb-4">
            <h3 class="mb-2 font-semibold">{{ __('contract.fields.scope_of_services.label') }}</h3>
            <p class="text-gray-700">{{ $contract->scope_of_services }}</p>
        </div>

        <!-- Contract Items -->
        @if ($contract->items->count())
            <div class="mb-4">
                <h3 class="mb-2 font-semibold">{{ __('contract.sections.items.label') }}</h3>
                <table class="min-w-full border border-collapse border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.description.label') }}
                            </th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.size.label') }}</th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.weight.label') }}</th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.quantity.label') }}</th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.unit_price.label') }}</th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.machine_count.label') }}
                            </th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.total_price.label') }}
                            </th>
                            <th class="px-3 py-2 border">{{ __('contract.fields.items.fields.total_weight.label') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contract->items as $item)
                            <tr>
                                <td class="px-3 py-2 border">{{ $item->description }}</td>
                                <td class="px-3 py-2 border">{{ $item->size }}</td>
                                <td class="px-3 py-2 border">{{ $item->weight }}</td>
                                <td class="px-3 py-2 border">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 border">{{ $item->unit_price }}</td>
                                <td class="px-3 py-2 border">{{ $item->machine_count }}</td>
                                <td class="px-3 py-2 border">{{ $item->total_price }}</td>
                                <td class="px-3 py-2 border">{{ $item->total_weight }} kg</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Documents -->
        @if ($contract->documents->count())
            <div class="mb-4 print:hidden">
                <h3 class="mb-2 font-semibold">{{ __('contract.sections.documents.label') }}</h3>
                <ul class="list-disc list-inside">
                    @foreach ($contract->documents as $doc)
                        <li>
                            {{ $doc->type ?? __('contract.fields.documents.default_label') }} -
                            {{ $doc->issuance_date?->format('Y-m-d') }} -
                            <a href="{{ $doc->getFirstMediaUrl('documents') }}" target="_blank"
                                class="text-blue-600 underline">{{ __('contract.fields.documents.view_file') }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Clauses -->
        <div class="sticky mb-6 ">
            <h3 class="mb-2 font-semibold">{{ __('contract.sections.clauses.label') }}</h3>
            <p><strong>{{ __('contract.fields.confidentiality_clause.label') }}:</strong>
                {{ $contract->confidentiality_clause }}</p>
            <p><strong>{{ __('contract.fields.termination_clause.label') }}:</strong>
                {{ $contract->termination_clause }}</p>
            <p><strong>{{ __('contract.fields.governing_law.label') }}:</strong> {{ $contract->governing_law }}</p>
        </div>

        <!-- Signature -->
        <div class="flex flex-row items-center justify-between mt-8 md:flex-row">
            <div class="w-full mx-5 text-center md:w-1/2">
                <p class="font-semibold">{{ __('contract.signature.authorized') }}</p>
                <div class="mt-12 border-t border-gray-600"></div>
                <p class="mt-2 text-gray-500">{{ $contract->company->name }}</p>
            </div>
            <div class="w-full mx-5 text-center md:w-1/2">
                <p class="font-semibold">{{ __('contract.signature.provider') }}</p>
                <div class="mt-12 border-t border-gray-600"></div>
                <p class="mt-2 text-gray-500">{{ __('contract.signature.placeholder') }}</p>
            </div>
        </div>
    </div>
</x-filament::page>
