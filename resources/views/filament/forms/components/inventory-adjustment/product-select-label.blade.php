<div class="flex items-center gap-2">
    @if ($image)
        <div class="relative overflow-hidden rounded-lg">
            <img 
                src="{{ asset('storage/' . $image) }}" 
                alt="{{ $name }}" 
                class="h-8 w-8 rounded object-cover"
            >
        </div>
    @else
        <div class="h-8 w-8 bg-gray-200 rounded flex items-center justify-center text-gray-500 text-xs">
            No Image
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-2">
            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $name }}</h3>
            <span class="text-sm text-emerald-600 bg-emerald-100 px-2 py-1 rounded-full">${{ number_format($price, 2) }}</span>
        </div>
        
        <div class="flex items-center gap-4 mt-1.5">
            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span class="font-medium">{{ $unit_type }}</span>
            </div>

            @if (isset($category_name) && $category_name)
                <div class="flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 5 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Zm10.125-7.81a1.5 1.5 0 0 1 1.062 1.063c.241.666.377 1.39.377 2.15l-.001.02a2.007 2.007 0 0 1-1.097 1.848 3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 15 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Z" />
                    </svg>
                    <span class="font-medium">{{ $category_name }}</span>
                </div>
            @endif

            @if (isset($subcategory_name) && $subcategory_name)
                <div class="flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 5 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Zm10.125-7.81a1.5 1.5 0 0 1 1.062 1.063c.241.666.377 1.39.377 2.15l-.001.02a2.007 2.007 0 0 1-1.097 1.848 3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 15 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Z" />
                    </svg>
                    <span class="font-medium">{{ $subcategory_name }}</span>
                </div>
            @endif

            @if (isset($brand_name) && $brand_name)
                <div class="flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 5 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Zm10.125-7.81a1.5 1.5 0 0 1 1.062 1.063c.241.666.377 1.39.377 2.15l-.001.02a2.007 2.007 0 0 1-1.097 1.848 3 3 0 0 0-5.82 1.164A2.007 2.007 0 0 1 15 19.143V20.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.357c0-.524.166-1.034.464-1.454a3 3 0 0 0 5.82-1.164Z" />
                    </svg>
                    <span class="font-medium">{{ $brand_name }}</span>
                </div>
            @endif
        </div>
    </div>
</div>