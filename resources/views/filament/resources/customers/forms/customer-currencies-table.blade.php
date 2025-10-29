<div class="bg-white sm:py-3">
    @if ($get('payer_currencies') && count($get('payer_currencies')) > 0)
        <div class="px-3 mx-auto max-w-7xl lg:px-2">
            <dl class="grid grid-rows-1 text-center md:grid-cols-5 lg:grid-cols-3">
                @foreach ($get('payer_currencies') as $currency)
                    <div class="flex flex-col max-w-xs mx-auto gap-y-2">
                        <dt class="text-gray-600 text-base/7">{{ $currency->currency->name }}</dt>
                        <dd class="order-first text-sm font-semibold tracking-tight text-gray-900 sm:text-xl">
                            {{ $currency->amount }}
                        </dd>
                    </div>
                @endforeach

                {{-- <div class="flex flex-col max-w-xs mx-auto gap-y-4">
                    <dt class="text-gray-600 text-base/7">Assets under holding</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900 sm:text-5xl">$119
                        trillion</dd>
                </div>
                <div class="flex flex-col max-w-xs mx-auto gap-y-4">
                    <dt class="text-gray-600 text-base/7">New users annually</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900 sm:text-5xl">46,000</dd>
                </div> --}}
            </dl>
        </div>
    @else
        <p class="text-center text-gray-500">No currencies available for this customer.</p>
    @endif
</div>
