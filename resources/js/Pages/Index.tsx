// resources/js/Pages/Index.tsx

import React, { useState, useMemo, useEffect, useRef } from 'react';
import { Head, usePage, useForm, router } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { ProductSelection } from '@/components/ProductSelection';
import { SaleCart } from '@/components/SaleCart';
import { Modal } from '@/components/ui/Modal';
import { CreateCustomerForm } from '@/components/CreateCustomerForm';
import { CreateDelivererForm } from '@/components/CreateDelivererForm';

// --- TYPE DEFINITIONS ---
type Batch = { id: number; batch_number: string; remaining_quantity: number; expiry_date: string | null; created_at: string; };
type Category = { id: number; name: string; };
type Subcategory = { id: number; name: string; category_id: number; };
type Brand = { id: number; name: string; };
type Product = {
  id: number;
  name: string;
  price: number | string;
  sku: string | null;
  barcode: string | null;
  unit_type: string;
  category_id: number | null;
  category: Category | null;
  subcategory_id: number | null;
  subcategory: Subcategory | null;
  brand_id: number | null;
  brand: Brand | null;
  batches: Batch[];
  attachments: string[] | null;
};
type Customer = { id: number; name: string; };
type Deliverer = { id: number; name: string; };
type CartItem = { product_id: number; name: string; quantity: number; unit_price: number; total: number; unit: string; selected_batches: { batch_id: number; quantity: number; }[]; };

// --- PROPS INTERFACE ---
interface PageProps {
  auth: any;
  products: Product[];
  customers: Customer[];
  deliverers: Deliverer[];
  categories: Category[];
  subcategories: Subcategory[];
  brands: Brand[];
  companyName: string;
  taxRate: number;
  defaultDeliveryFee: number; // Add defaultDeliveryFee
}

function usePrevious<T>(value: T): T | undefined {
  const ref = useRef<T>();
  useEffect(() => { ref.current = value; });
  return ref.current;
}

export default function Index() {
  const { auth, products, customers, deliverers, categories, subcategories, brands, companyName, taxRate, defaultDeliveryFee } = usePage<PageProps>().props;
  const prevCustomers = usePrevious(customers);
  const prevDeliverers = usePrevious(deliverers);

  // --- STATE MANAGEMENT ---
  const [cart, setCart] = useState<CartItem[]>([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedSubcategory, setSelectedSubcategory] = useState<number | null>(null);
  const [selectedBrand, setSelectedBrand] = useState<number | null>(null);
  const [selectedCustomerId, setSelectedCustomerId] = useState<number | null>(null);
  const [selectedDelivererId, setSelectedDelivererId] = useState<number | null>(null);
  const [discount, setDiscount] = useState(0);
  const [deliveryFee, setDeliveryFee] = useState(defaultDeliveryFee); // Use defaultDeliveryFee
  const [isCustomerModalOpen, setIsCustomerModalOpen] = useState(false);
  const [isDelivererModalOpen, setIsDelivererModalOpen] = useState(false);

  // --- FORM HANDLING ---
  const { data, setData, post, processing, reset } = useForm({
    customer_id: null as number | null,
    deliverer_id: null as number | null,
    items: [] as CartItem[],
    subtotal: 0,
    discount: 0,
    delivery_fee: 0,
    tax: 0,
    total_amount: 0,
    payment_method: 'cash',
    notes: '',
  });

  const parseNum = (val: number | string) => typeof val === 'string' ? parseFloat(val) : (val || 0);
  const subtotal = useMemo(() => cart.reduce((acc, item) => acc + parseNum(item.total), 0), [cart]);
  const tax = useMemo(() => (subtotal - discount) * taxRate, [subtotal, discount, taxRate]);
  const totalAmount = useMemo(() => (subtotal - discount) + tax + parseNum(deliveryFee), [subtotal, discount, tax, deliveryFee]);

  // --- EFFECTS ---
  const resetPage = () => {
    setCart([]);
    setSelectedCustomerId(null);
    setSelectedDelivererId(null);
    setDiscount(0);
    setDeliveryFee(defaultDeliveryFee); // Reset to default
    setSearchQuery('');
    setSelectedCategory(null);
    setSelectedSubcategory(null);
    setSelectedBrand(null);
    reset();
  };

  useEffect(() => {
    if (prevCustomers && customers.length > prevCustomers.length) {
      const newCustomer = customers.find(c => !prevCustomers.some(pc => pc.id === c.id));
      if (newCustomer) {
        setSelectedCustomerId(newCustomer.id);
        toast.info(`Selected newly created customer: ${newCustomer.name}`);
      }
    }
  }, [customers, prevCustomers]);

  useEffect(() => {
    if (prevDeliverers && deliverers.length > prevDeliverers.length) {
      const newDeliverer = deliverers.find(d => !prevDeliverers.some(pd => pd.id === d.id));
      if (newDeliverer) {
        setSelectedDelivererId(newDeliverer.id);
        toast.info(`Selected newly created deliverer: ${newDeliverer.name}`);
      }
    }
  }, [deliverers, prevDeliverers]);

  useEffect(() => {
    const handleNavigate = () => resetPage();
    router.on('navigate', handleNavigate);
    return () => router.off('navigate', handleNavigate);
  }, []);

  useEffect(() => {
    setData({
      ...data,
      customer_id: selectedCustomerId,
      deliverer_id: selectedDelivererId,
      items: cart.map(item => ({ ...item, selected_batches: allocateBatches(item) })),
      subtotal,
      discount: parseNum(discount),
      delivery_fee: parseNum(deliveryFee),
      tax,
      total_amount: totalAmount,
    });
  }, [cart, selectedCustomerId, selectedDelivererId, subtotal, discount, deliveryFee, tax, totalAmount]);

  // --- HANDLER FUNCTIONS ---
  const handleAddToCart = (product: Product) => {
    const existingItem = cart.find(item => item.product_id === product.id);
    const availableStock = product.batches.reduce((acc, batch) => acc + batch.remaining_quantity, 0);

    if (existingItem) {
      const newQuantity = existingItem.quantity + 1;
      if (newQuantity > availableStock) {
        toast.error(`Not enough stock for ${product.name}. Only ${availableStock} available.`);
        return;
      }
      updateCartItemQuantity(product.id, newQuantity);
    } else {
      if (availableStock < 1) {
        toast.error(`${product.name} is out of stock.`);
        return;
      }
      const priceAsNumber = parseNum(product.price);
      setCart([...cart, {
        product_id: product.id,
        name: product.name,
        quantity: 1,
        unit_price: priceAsNumber,
        total: priceAsNumber,
        unit: product.unit_type,
        selected_batches: [],
      }]);
    }
  };

  const handleBarcodeScan = (barcode: string) => {
    const normalizedBarcode = barcode.trim().toLowerCase();
    console.log('Scanned barcode:', normalizedBarcode);
    const product = products.find(p => p.barcode && p.barcode.toLowerCase() === normalizedBarcode);
    if (product) {
      handleAddToCart(product);
      toast.success(`Added ${product.name} to cart.`);
    } else {
      toast.error(`Product with barcode "${barcode}" not found.`);
    }
  };

  const updateCartItemQuantity = (productId: number, newQuantity: number) => {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    const availableStock = product.batches.reduce((acc, batch) => acc + batch.remaining_quantity, 0);

    if (newQuantity > availableStock) {
      toast.error(`Not enough stock for ${product.name}. Only ${availableStock} available.`);
      return;
    }

    if (newQuantity <= 0) {
      setCart(cart.filter(item => item.product_id !== productId));
    } else {
      setCart(cart.map(item => item.product_id === productId ? { ...item, quantity: newQuantity, total: newQuantity * parseNum(item.unit_price) } : item));
    }
  };

  const allocateBatches = (item: CartItem) => {
    const product = products.find(p => p.id === item.product_id);
    if (!product) return [];
    let quantityToAllocate = item.quantity;
    const allocatedBatches = [];
    const sortedBatches = [...product.batches].sort((a, b) => new Date(a.expiry_date || a.created_at).getTime() - new Date(b.expiry_date || b.created_at).getTime());

    for (const batch of sortedBatches) {
      if (quantityToAllocate <= 0) break;
      const quantityFromThisBatch = Math.min(quantityToAllocate, batch.remaining_quantity);
      allocatedBatches.push({ batch_id: batch.id, quantity: quantityFromThisBatch });
      quantityToAllocate -= quantityFromThisBatch;
    }
    return allocatedBatches;
  };

  const handleSubmitSale = (e: React.FormEvent) => {
    e.preventDefault();
    e.stopPropagation();
    console.log('Submitting sale:', data);
    if (cart.length === 0) {
      toast.error("Cart cannot be empty.");
      return;
    }

    post('/sales', {
      onSuccess: () => {
        toast.success("Sale recorded successfully!");
        resetPage();
      },
      onError: (errors) => {
        console.error("Submission Errors:", errors);
        const firstError = Object.values(errors)[0];
        toast.error(firstError || "An unknown error occurred.");
      },
      preserveScroll: true,
    });
  };

  const filteredProducts = useMemo(() => {
    if (!products) return [];
    return products.filter(product => {
      const matchesSearchQuery = searchQuery === '' ||
        product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (product.sku && product.sku.toLowerCase().includes(searchQuery.toLowerCase()));
      const matchesCategory = selectedCategory === null || (product.category_id !== null && product.category_id === selectedCategory);
      const matchesSubcategory = selectedSubcategory === null ||
        (product.subcategory_id !== null && product.subcategory_id === selectedSubcategory &&
         (selectedCategory === null || product.category_id === selectedCategory));
      const matchesBrand = selectedBrand === null || (product.brand_id !== null && product.brand_id === selectedBrand);
      return matchesSearchQuery && matchesCategory && matchesSubcategory && matchesBrand;
    });
  }, [products, searchQuery, selectedCategory, selectedSubcategory, selectedBrand]);

  // --- RENDER ---
  return (
    <>
      <Head title="Cash Register" />
      <Toaster richColors position="top-right" />
      
      <Modal isOpen={isCustomerModalOpen} onClose={() => setIsCustomerModalOpen(false)} title="Create New Customer">
        <CreateCustomerForm onSuccess={() => setIsCustomerModalOpen(false)} />
      </Modal>
      <Modal isOpen={isDelivererModalOpen} onClose={() => setIsDelivererModalOpen(false)} title="Create New Deliverer">
        <CreateDelivererForm onSuccess={() => setIsDelivererModalOpen(false)} />
      </Modal>

      <div className="min-h-screen bg-gray-100 p-4">
        <div>
          <header className="max-w-7xl mx-auto mb-6 flex justify-between items-center">
            <div>
              <h1 className="text-2xl font-bold text-gray-800">{companyName || 'Your Company'}</h1>
              <p className="text-sm text-gray-500">Cash Register</p>
            </div>
            <a href="/admin" className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
              Back to Admin
            </a>
          </header>
          
          <form onSubmit={handleSubmitSale} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <ProductSelection
              products={filteredProducts}
              categories={categories || []}
              subcategories={subcategories || []}
              brands={brands || []}
              searchQuery={searchQuery}
              selectedCategory={selectedCategory}
              selectedSubcategory={selectedSubcategory}
              selectedBrand={selectedBrand}
              onSearchChange={setSearchQuery}
              onCategoryChange={setSelectedCategory}
              onSubcategoryChange={setSelectedSubcategory}
              onBrandChange={setSelectedBrand}
              onProductClick={handleAddToCart}
              onBarcodeScan={handleBarcodeScan}
            />
            <SaleCart
              cart={cart}
              customers={customers}
              deliverers={deliverers}
              selectedCustomerId={selectedCustomerId}
              selectedDelivererId={selectedDelivererId}
              subtotal={subtotal}
              discount={discount}
              deliveryFee={deliveryFee}
              tax={tax}
              totalAmount={totalAmount}
              paymentMethod={data.payment_method}
              processing={processing}
              taxRate={taxRate} // Pass taxRate
              defaultDeliveryFee={defaultDeliveryFee} // Pass defaultDeliveryFee
              onCustomerChange={setSelectedCustomerId}
              onDelivererChange={setSelectedDelivererId}
              onQuantityChange={updateCartItemQuantity}
              onDiscountChange={setDiscount}
              onDeliveryFeeChange={setDeliveryFee}
              onPaymentMethodChange={(method) => setData('payment_method', method)}
              onOpenNewCustomerModal={() => setIsCustomerModalOpen(true)}
              onOpenNewDelivererModal={() => setIsDelivererModalOpen(true)}
            />
          </form>
        </div>
      </div>
    </>
  );
}