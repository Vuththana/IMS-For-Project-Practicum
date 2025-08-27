<div class="flex items-center gap-2">
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-2">
            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $batch_number }}</h3>
            <span class="text-sm text-emerald-600 bg-emerald-100 px-2 py-1 rounded-full">${{ number_format($cost_price, 2) }}</span>
        </div>
        
        <div class="flex items-center gap-4 mt-1.5">
            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span class="{{ $remaining_quantity > 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                    {{ $remaining_quantity }}
                </span>
            </div>
            
            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-medium">{{ $expiry_date ? $expiry_date->format('M d, Y') : 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>