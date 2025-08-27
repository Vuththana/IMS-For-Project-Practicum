<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg print:shadow-none p-4 sm:p-6 lg:p-8">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start pb-6 mb-6 border-b border-gray-200 dark:border-gray-700">
            <div class="w-full sm:w-3/5 mb-4 sm:mb-0">
                @if($purchase->company?->profile?->logo)
                    <img src="{{ asset('storage/' . $purchase->company->profile->logo) }}"
                         alt="{{ $purchase->company->name ?? 'Company' }} Logo"
                         class="h-16 md:h-20 w-auto object-contain mb-4 rounded"
                         onerror="this.style.display='none';">
                @endif
                <h2 class="text-2xl font-semibold text-primary-600 dark:text-primary-500">{{ $purchase->company->name ?? 'Your Company Name' }}</h2>
                @if($purchase->company?->profile)
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 space-y-0.5">
                        <p>{{ $purchase->company->profile->street_address ?? '' }}</p>
                        <p>
                            {{ $purchase->company->profile->city ?? '' }}{{ ($purchase->company->profile->city && $purchase->company->profile->state) ? ', ' : '' }}{{ $purchase->company->profile->state ?? '' }}
                            {{ $purchase->company->profile->postal_code ?? '' }}
                        </p>
                        <p>{{ $purchase->company->profile->country ?? '' }}</p>
                        @if($purchase->company->profile->phone_number)
                            <p class="flex items-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ $purchase->company->profile->phone_number }}
                            </p>
                        @endif
                        @if($purchase->company->profile->email)
                            <p class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                {{ $purchase->company->profile->email }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="w-full sm:w-2/5 text-left sm:text-right">
                <h1 class="text-3xl sm:text-4xl font-bold text-primary-700 dark:text-primary-400">PURCHASE ORDER</h1>
                <div class="mt-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-700 dark:text-gray-300">PO #:</span> {{ $purchase->reference }}</p>
                    <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-700 dark:text-gray-300">Order Date:</span> {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('F d, Y') }}</p>
                    <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-700 dark:text-gray-300">Expected Delivery:</span> {{ \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('F d, Y') }}</p>
                    @if ($purchase->status)
                        <p class="mt-1">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Status:</span>
                            <x-filament::badge
                                :color="match ($purchase->status instanceof \App\Enums\Purchase\PurchaseStatus ? $purchase->status->value : $purchase->status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'draft' => 'gray',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                }">
                                {{ $purchase->status instanceof \App\Enums\Purchase\PurchaseStatus ? $purchase->status->getLabel() : ucfirst(str_replace('_', ' ', $purchase->status)) }}
                            </x-filament::badge>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Supplier & Shipping Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Supplier</h3>
                <div class="p-4 rounded-lg text-sm">
                    @if ($purchase->supplier)
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $purchase->supplier->name }}</p>
                        @if ($purchase->supplier->address_line_1) <p class="text-gray-600 dark:text-gray-400">{{ $purchase->supplier->address_line_1 }}</p> @endif
                        @if ($purchase->supplier->city || $purchase->supplier->state || $purchase->supplier->postal_code)
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ $purchase->supplier->city ?? '' }}{{ ($purchase->supplier->city && $purchase->supplier->state) ? ', ' : '' }}{{ $purchase->supplier->state ?? '' }} {{ $purchase->supplier->postal_code ?? '' }}
                            </p>
                        @endif
                        @if ($purchase->supplier->country) <p class="text-gray-600 dark:text-gray-400">{{ $purchase->supplier->country }}</p> @endif

                        @if($purchase->supplier->phone)
                            <p class="flex items-center mt-1 text-gray-600 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ $purchase->supplier->phone }}
                            </p>
                        @endif
                        @if($purchase->supplier->email)
                            <p class="flex items-center text-gray-600 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                {{ $purchase->supplier->email }}
                            </p>
                        @endif
                    @else
                        <p class="text-gray-600 dark:text-gray-400">No Supplier Selected</p>
                    @endif
                </div>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Shipping Details</h3>
                <div class="p-4 rounded-lg text-sm">
                    <p class="text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Ship to:</span> {{ $purchase->shipping_address ?? 'Main Warehouse' }} <br>
                        <span class="font-medium">Carrier:</span> {{ $purchase->carrier ?? 'N/A' }} <br>
                        <span class="font-medium">Tracking #:</span> {{ $purchase->tracking_number ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mb-8">
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[5%]">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[40%]">Product</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[15%]">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[20%]">Unit Cost</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[20%]">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php $itemCounter = 1; @endphp
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $itemCounter++ }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ $item->product->name ?? 'N/A' }}</div>
                                    @if ($item->product?->description)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($item->product->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals Section & Payment Terms --}}
        <div class="flex flex-col md:flex-row justify-between items-start gap">
            <div class="w-full md:w-1/2">
                @if ($purchase->payment_terms)
                <div class="p-4 sm:p-6 rounded-lg space-y-2 text-sm">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Payment Terms</h3>
                    <div class="flex items-center text-sm">
                        <x-filament::icon
                            icon="heroicon-o-currency-dollar"
                            class="h-5 w-5 text-primary-600 dark:text-primary-500 mr-2"
                        />
                        <span class="font-medium text-gray-800 dark:text-gray-200">
                            Net {{ $purchase->payment_terms }} Days
                        </span>
                    </div>
                    @if($purchase->due_date)
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Due Date: {{ $purchase->due_date->format('M d, Y') }}
                    </div>
                    @endif
                </div>
                @endif

                @if(!empty($purchase->notes))
                <div class="mt-4 rounded-lg">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Notes</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 italic">{{ $purchase->notes }}</p>
                </div>
                @endif
            </div>

            <div class="w-full md:w-auto md:min-w-[280px] lg:min-w-[320px]">
                <div class="p-4 sm:p-6 rounded-lg space-y-2 text-sm">
                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                        <span>Subtotal:</span>
                        <span class="font-medium">${{ number_format($items->sum('total'), 2) }}</span>
                    </div>
                    @if(isset($purchase->discount) && $purchase->discount > 0)
                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                        <span>Discount:</span>
                        <span class="font-medium text-red-600 dark:text-red-400">-${{ number_format($purchase->discount, 2) }}</span>
                    </div>
                    @endif
                    @if(isset($purchase->tax) && $purchase->tax > 0)
                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                        <span>Tax:</span>
                        <span class="font-medium">${{ number_format($purchase->tax, 2) }}</span>
                    </div>
                    @endif
                    @if(isset($purchase->delivery_fee) && $purchase->delivery_fee > 0)
                    <div class="flex justify-between text-gray-700 dark:text-gray-300 pb-2 border-b border-gray-300 dark:border-gray-600">
                        <span>Delivery Fee:</span>
                        <span class="font-medium">${{ number_format($purchase->delivery_fee, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 text-gray-900 dark:text-gray-100">
                        <span class="text-base font-bold">TOTAL:</span>
                        <span class="text-base font-bold text-primary-600 dark:text-primary-500">${{ number_format($purchase->total_cost, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-500 dark:text-gray-400 pt-8 mt-8 border-t border-gray-200 dark:border-gray-700">
            <p>This is an official purchase order from {{ $purchase->company->name ?? 'Your Company' }}</p>
            <p class="mt-1">Please notify us immediately of any discrepancies</p>
            <p class="mt-1">
                {{ $purchase->company->name ?? 'Your Company' }}
                @if($purchase->company?->profile?->phone_number) | {{ $purchase->company->profile->phone_number }} @endif
            </p>
        </div>
    </div>
</x-filament-panels::page>