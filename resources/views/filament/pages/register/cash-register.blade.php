<x-filament-panels::page
    x-data="{
        focusBarcodeScanner() {
            $nextTick(() => {
                const barcodeInput = document.getElementById('barcode_scanner_input');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }
            });
        }
    }"
    @focus-barcode-scanner.window="focusBarcodeScanner()"
>

    {{-- Modal for Opening Register --}}
    <x-filament::modal id="confirm-open-register" width="lg">
        <x-slot name="trigger"></x-slot>
        <x-slot name="heading">Open Cash Register</x-slot>
        <form wire:submit.prevent="confirmOpenRegister">
            <div class="p-4 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Register Mode</label>
                    <fieldset class="mt-2">
                        <legend class="sr-only">Register mode</legend>
                        <div class="space-y-2 sm:space-y-0 sm:flex sm:gap-x-4">
                            <div class="flex items-center">
                                <input id="mode_guest" wire:model.defer="openRegisterData.mode" name="register_mode_option" type="radio" value="guest"
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-primary-500 dark:focus:ring-primary-600">
                                <label for="mode_guest" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    Guest Mode
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="mode_customer" wire:model.defer="openRegisterData.mode" name="register_mode_option" type="radio" value="customer"
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-primary-500 dark:focus:ring-primary-600">
                                <label for="mode_customer" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    Customer Mode
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('openRegisterData.mode') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>

                {{-- << NEW: Sale Type Selection >> --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Sale Type</label>
                    <fieldset class="mt-2">
                        <legend class="sr-only">Sale type</legend>
                        <div class="space-y-2 sm:space-y-0 sm:flex sm:gap-x-4">
                            <div class="flex items-center">
                                <input id="sale_type_physical" wire:model.defer="openRegisterData.sale_type_modal" name="sale_type_option" type="radio" value="physical"
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-primary-500 dark:focus:ring-primary-600">
                                <label for="sale_type_physical" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    Physical Sale
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="sale_type_online" wire:model.defer="openRegisterData.sale_type_modal" name="sale_type_option" type="radio" value="online"
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-primary-500 dark:focus:ring-primary-600">
                                <label for="sale_type_online" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    Online Sale
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('openRegisterData.sale_type_modal') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>
                {{-- << END NEW: Sale Type Selection >> --}}


                <div>
                    <label for="starting_balance_modal_input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Starting Balance</label>
                    <input type="number" id="starting_balance_modal_input" wire:model.defer="openRegisterData.starting_balance_modal" step="0.01" min="0" required
                           class="block w-full mt-1 text-gray-900 dark:text-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @error('openRegisterData.starting_balance_modal') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-x-3">
                    <x-filament::button type="button" color="gray" x-on:click="$dispatch('close-modal', { id: 'confirm-open-register' })">Cancel</x-filament::button>
                    <x-filament::button type="submit" color="success" wire:click="confirmOpenRegister">
                        Confirm & Open Register
                    </x-filament::button>
                </div>
            </x-slot>
        </form>
    </x-filament::modal>

    {{-- Main Content Area --}}
    @if ($this->registerOpen)
        {{-- << MODIFIED: Added Sale Type to display >> --}}
        <div class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
            Operating Mode: <span class="text-primary-600 dark:text-primary-500">{{ ucfirst($this->currentRegisterMode) }}</span> |
            Sale Type: <span class="text-primary-600 dark:text-primary-500">{{ ucfirst($this->currentSaleType) }}</span> |
            Starting Balance: {{ $this->formatCurrency($this->startingBalance) }}
        </div>

        {{-- Customer, Deliverer, and Delivery Status Section --}}
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow space-y-4">
            @if ($this->currentRegisterMode === 'customer')
                <div>
                    <label for="customer_id_select" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Select Customer:</label>
                    <select wire:model.live="customerId" id="customer_id_select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">-- Select Customer --</option>
                        @foreach (\App\Models\Partner\Customer::orderBy('name')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @if (is_null($customerId) && $this->currentRegisterMode === 'customer')
                        <p class="mt-2 text-sm text-warning-600 dark:text-warning-500">Customer selection is required in Customer Mode.</p>
                    @endif
                    @error('customerId') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
                </div>
            @endif

            <div>
                <label for="deliverer_id_select" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Select Deliverer (Optional):</label>
                <select wire:model.live="delivererId" id="deliverer_id_select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">-- No Deliverer --</option>
                    @foreach (\App\Models\User::whereHas('roles', function ($query) { $query->where('name', 'Deliverer'); })->orderBy('name')->get() as $deliverer)
                        <option value="{{ $deliverer->id }}">{{ $deliverer->name }}</option>
                    @endforeach
                </select>
                @error('delivererId') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="delivery_status_select" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Delivery Status:</label>
                <select wire:model.live="currentDeliveryStatus" id="delivery_status_select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="pending">Pending</option>
                    <option value="delivered">Delivered / Take-away</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                @error('currentDeliveryStatus') <span class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</span> @enderror
            </div>
        </div>


        {{-- Main POS Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                {{-- Barcode and Product Search --}}
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="barcode_scanner_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scan Barcode (Press Enter)</label>
                            <input type="text" id="barcode_scanner_input"
                                   wire:model.lazy="scannedBarcode"
                                   wire:keydown.enter="processBarcodeScan"
                                   placeholder="Scan or type barcode..."
                                   class="mt-1 block w-full pl-3 pr-3 py-2 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md shadow-sm"
                                   x-init="$nextTick(() => { if (document.getElementById('barcode_scanner_input')) document.getElementById('barcode_scanner_input').focus() })">
                        </div>
                        <div>
                            <label for="product_search_query" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Products</label>
                            <input type="text" id="product_search_query"
                                   wire:model.live.debounce.300ms="productSearchQuery"
                                   placeholder="Search by name or barcode..."
                                   class="mt-1 block w-full pl-3 pr-3 py-2 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Product Grid --}}
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow rounded-lg min-h-[300px] max-h-[60vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Products</h3>
                    @if ($this->getFilteredProductsProperty() && $this->getFilteredProductsProperty()->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach ($this->getFilteredProductsProperty() as $product)
                                <button type="button" wire:click="addToCart({{ $product->id }})"
                                        class="block border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-150 ease-in-out dark:hover:border-gray-600"
                                        title="Add {{ $product->name }} to cart"
                                        @disabled($product->total_remaining_stock <= 0)>
                                    <div class="aspect-w-1 aspect-h-1 bg-gray-50 dark:bg-gray-700">
                                        @if ($product->attachments && (is_string($product->attachments) || (is_array($product->attachments) && !empty($product->attachments[0]))))
                                            @php
                                                $imageUrl = is_array($product->attachments) ? $product->attachments[0] : $product->attachments;
                                            @endphp
                                            <img src="{{ asset('storage/' . $imageUrl) }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
                                        @else
                                            <div class="flex items-center justify-center w-full h-full bg-gray-200 dark:bg-gray-600">
                                                <svg class="w-1/2 h-1/2 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2 sm:p-3 text-center">
                                        <p class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ $product->name }}</p>
                                        
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $this->formatCurrency($product->price) }} / {{ $product->unit_type }}
                                        </p>
                                        
                                        @if($product->total_remaining_stock <= 0)
                                            <span class="text-xs text-red-500 dark:text-red-400 font-semibold">Out of Stock</span>
                                        @elseif($product->total_remaining_stock < 10)
                                            <span class="text-xs text-amber-600 dark:text-amber-500 font-semibold">Low Stock ({{$product->total_remaining_stock}})</span>
                                        @else
                                            <span class="text-xs text-green-600 dark:text-green-500">In Stock ({{$product->total_remaining_stock}})</span>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @elseif(!empty($this->productSearchQuery))
                        <p class="text-gray-500 dark:text-gray-400">No products found matching your search "{{ $this->productSearchQuery }}".</p>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No products available or matching criteria. Ensure products are sellable and have batches with stock.</p>
                    @endif
                </div>
            </div>

            {{-- Right Column: Cart & Payment --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow rounded-lg max-h-[calc(var(--screen-height,100vh)-280px)] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 sticky top-0 bg-white dark:bg-gray-800 py-2 z-10">Shopping Cart
                        @if(!empty($this->cart)) ({{ count($this->cart) }} items) @endif
                    </h3>
                    @if (empty($this->cart))
                        <div class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-16 h-16 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Your cart is empty.</span>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($this->cart as $index => $item)
                                <div wire:key="cart-item-{{ $index }}" class="flex items-center justify-between p-2.5 border border-gray-200 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div class="flex items-center gap-3 flex-grow min-w-0">
                                        @if ($item['attachments'] && (is_string($item['attachments']) || (is_array($item['attachments']) && !empty($item['attachments'][0]))))
                                               @php
                                                    $itemImageUrl = is_array($item['attachments']) ? $item['attachments'][0] : $item['attachments'];
                                               @endphp
                                            <img src="{{ asset('storage/' . $itemImageUrl) }}" alt="{{ $item['name'] }}" class="w-10 h-10 object-cover rounded flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-400 dark:text-gray-500 flex-shrink-0">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                            </div>
                                        @endif
                                        <div class="flex-grow overflow-hidden">
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $item['display_name'] }}</p>
                                            
                                            <p class="text-xs text-gray-600 dark:text-gray-400 inline-flex items-center">
                                                <input type="number" wire:model.lazy="cart.{{ $index }}.quantity"
                                                       wire:change="updateCartItemQuantity({{ $index }}, $event.target.value)"
                                                       min="0"
                                                       max="{{ $item['stock_available_in_batch'] ?? 1 }}"
                                                       class="w-16 p-1 text-center border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:ring-primary-500 focus:border-primary-500 text-xs"
                                                       aria-label="Quantity for {{ $item['display_name'] }}">
                                                <span class="mx-1 font-semibold text-gray-500 dark:text-gray-400">{{ $item['unit_type'] }}</span>
                                                <span>x {{ $this->formatCurrency($item['price']) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end flex-shrink-0 ml-2">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $this->formatCurrency($item['item_total']) }}</p>
                                        <button type="button" wire:click="removeCartItem({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 text-xs mt-1" aria-label="Remove {{ $item['display_name'] }}">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300"><span>Subtotal</span><span>{{ $this->formatCurrency($this->total) }}</span></div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 pt-2 border-t dark:border-gray-700"><span>TOTAL</span><span>{{ $this->formatCurrency($this->total) }}</span></div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label for="payment_method_select" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <select wire:model.live="paymentMethod" id="payment_method_select" class="mt-1 block w-full text-gray-900 dark:text-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        @if ($this->paymentMethod === 'cash')
                            <div>
                                <label for="cash_received_input" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Cash Received</label>
                                <input type="number" wire:model.lazy="cashReceived" id="cash_received_input" step="0.01" min="0" class="mt-1 block w-full text-gray-900 dark:text-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="0.00">
                                @error('cashReceived') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="mt-1 text-md font-medium text-gray-700 dark:text-gray-200">Change: {{ $this->formatCurrency($this->change) }}</div>
                        @endif
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <x-filament::button type="button" color="warning" wire:click="clearCart" wire:loading.attr="disabled" class="w-full justify-center">Clear Cart</x-filament::button>
                        <x-filament::button type="button" color="success" wire:click="processPayment" wire:loading.attr="disabled" class="w-full justify-center">Process Payment</x-filament::button>
                    </div>
                </div>
            </div>
        </div>

    @else {{-- Register is not open --}}
        {{ $this->form }}
    @endif

</x-filament-panels::page>