// resources/js/components/ProductSelection.tsx

import React, { useEffect, useMemo, useRef } from 'react';

// --- TYPE DEFINITIONS (Matching your latest schema) ---
type Category = {
  id: number;
  name: string;
};

type Subcategory = {
  id: number;
  name: string;
  category_id: number; // Crucial for cascading logic
};


type Brand = {
  id: number;
  name: string;
};

type Product = {
  id: number;
  name: string;
  price: number | string;
  sku: string | null;
  unit_type: string | null;
  barcode: string | null;
  attachments: string[] | null;
  category: Category | null;
  subcategory: Subcategory | null;
  brand: Brand | null;
};

function ProductCard({ product, onClick }: { product: Product, onClick: () => void }) {
  const imageUrl =
      product.attachments && product.attachments.length > 0
          ? `/storage/${product.attachments}`
          : 'https://via.placeholder.com/150';

  const formattedPrice = (
      (typeof product.price === 'string'
          ? parseFloat(product.price)
          : product.price) || 0
  ).toFixed(2);

  return (
      <div
          onClick={onClick}
          className="border rounded-lg p-4 flex flex-col items-center text-center cursor-pointer hover:shadow-xl hover:border-blue-500 transition-all duration-200"
      >
          <img
              src={imageUrl}
              alt={product.name}
              className="w-24 h-24 object-cover mb-2 rounded"
          />
          <p className="font-semibold text-sm flex-grow">{product.name} ({product.sku})</p>
          <p className="text-xs text-gray-500">${formattedPrice} / {product.unit_type}</p>
      </div>
  );
}

// --- MAIN COMPONENT: ProductSelection ---
type Props = {
  products: Product[];
  categories: Category[];
  subcategories: Subcategory[]; // NEW
  brands: Brand[]; // NEW
  searchQuery: string;
  selectedCategory: number | null;
  selectedSubcategory: number | null; // NEW
  selectedBrand: number | null; // NEW
  onSearchChange: (query: string) => void;
  onCategoryChange: (categoryId: number | null) => void;
  onSubcategoryChange: (subcategoryId: number | null) => void; // NEW
  onBrandChange: (brandId: number | null) => void; // NEW
  onProductClick: (product: Product) => void;
  onBarcodeScan: (barcode: string) => void;
};

export function ProductSelection({
  products,
  categories,
  subcategories, 
  brands,
  searchQuery,
  selectedCategory,
  selectedSubcategory, 
  selectedBrand,
  onSearchChange,
  onCategoryChange,
  onSubcategoryChange,
  onBrandChange,
  onProductClick,
  onBarcodeScan,
}: Props) {

  // --- NEW: Cascading Subcategory Logic ---
  // Memoize the list of subcategories that belong to the selected parent category.
  const visibleSubcategories = useMemo(() => {
      if (!selectedCategory) return [];
      return subcategories.filter(sc => sc.category_id === selectedCategory);
  }, [selectedCategory, subcategories]);

  const handleCategoryChange = (categoryId: number | null) => {
      onCategoryChange(categoryId);
      onSubcategoryChange(null);
  };
  const barcodeInputRef = useRef<HTMLInputElement>(null);
  const timeoutRef = useRef<NodeJS.Timeout | null>(null);

  const handleBarcodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.trim();
    console.log('Barcode input change:', value);
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }
    timeoutRef.current = setTimeout(() => {
      if (value) {
        console.log('Barcode scanned (onChange):', value);
        onBarcodeScan(value);
        if (barcodeInputRef.current) {
          barcodeInputRef.current.value = '';
        }
      }
    }, 300);
  };
  const handleBarcodeSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const barcodeInput = e.currentTarget.elements.namedItem('barcode') as HTMLInputElement;
    if (barcodeInput.value) {
      console.log('Barcode submitted:', barcodeInput.value);
      onBarcodeScan(barcodeInput.value.trim());
      barcodeInput.value = '';
    }
  };
  useEffect(() => {
    if (barcodeInputRef.current) {
      barcodeInputRef.current.focus();
    }
  }, []);
  return (
    <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-lg">
      {/* Search and Barcode Section */}
      <div className="flex flex-col md:flex-row gap-4 mb-4">
        <form onSubmit={handleBarcodeSubmit} className="flex-1">
          <label htmlFor="barcode" className="sr-only">Scan Barcode</label>
          <input
            id="barcode"
            name="barcode"
            type="text"
            placeholder="Scan Barcode..."
            className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            autoFocus 
            ref={barcodeInputRef}
            onChange={handleBarcodeChange}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault(); // Prevent Enter from submitting outside the form
              }
            }}
          />
        </form>
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => onSearchChange(e.target.value)}
          placeholder="Search products by name or SKU..."
          className="flex-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      {/* Category Filters */}
      <div className="space-y-4 mb-6">
                {/* Category Filters */}
                <div className="flex flex-wrap gap-2">
                    <button
                        onClick={() => handleCategoryChange(null)}
                        className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedCategory === null ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                    >
                        All Categories
                    </button>
                    {categories.map(category => (
                        <button
                            key={category.id}
                            onClick={() => handleCategoryChange(category.id)}
                            className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedCategory === category.id ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                        >
                            {category.name}
                        </button>
                    ))}
                </div>

                {/* --- NEW: Subcategory Filters (Conditional) --- */}
                {selectedCategory && visibleSubcategories.length > 0 && (
                    <div className="flex flex-wrap gap-2 border-t pt-4">
                        <button
                            onClick={() => onSubcategoryChange(null)}
                            className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedSubcategory === null ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                        >
                            All Subcategories
                        </button>
                        {visibleSubcategories.map(subcategory => (
                            <button
                                key={subcategory.id}
                                onClick={() => onSubcategoryChange(subcategory.id)}
                                className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedSubcategory === subcategory.id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                            >
                                {subcategory.name}
                            </button>
                        ))}
                    </div>
                )}

                {/* --- NEW: Brand Filters --- */}
                <div className="flex flex-wrap gap-2 border-t pt-4">
                    <button
                        onClick={() => onBrandChange(null)}
                        className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedBrand === null ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                    >
                        All Brands
                    </button>
                    {brands.map(brand => (
                        <button
                            key={brand.id}
                            onClick={() => onBrandChange(brand.id)}
                            className={`px-4 py-2 rounded-full text-sm font-semibold transition-colors ${selectedBrand === brand.id ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
                        >
                            {brand.name}
                        </button>
                    ))}
                </div>
            </div>

      {/* Product Grid */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[70vh] overflow-y-auto pr-2">
        {products.length > 0 ? (
          products.map((product) => (
            <ProductCard
              key={product.id}
              product={product}
              onClick={() => onProductClick(product)}
            />
          ))
        ) : (
          <p className="col-span-full text-center text-gray-500 mt-8">
            No products to display.
          </p>
        )}
      </div>
    </div>
  );
}