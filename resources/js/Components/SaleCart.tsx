// resources/js/components/SaleCart.tsx

import React from 'react';

// --- TYPE DEFINITIONS ---
type CartItem = {
  product_id: number;
  name: string;
  quantity: number;
  unit_price: number | string;
  total: number | string;
};
type Customer = { id: number; name: string };
type Deliverer = { id: number; name: string };

type Props = {
  cart: CartItem[];
  customers: Customer[];
  deliverers: Deliverer[];
  selectedCustomerId: number | null;
  selectedDelivererId: number | null;
  subtotal: number;
  discount: number;
  deliveryFee: number;
  tax: number;
  totalAmount: number;
  paymentMethod: string;
  processing: boolean;
  taxRate: number; // Receive taxRate
  defaultDeliveryFee: number; // Receive defaultDeliveryFee
  onCustomerChange: (id: number | null) => void;
  onDelivererChange: (id: number | null) => void;
  onQuantityChange: (productId: number, newQuantity: number) => void;
  onDiscountChange: (amount: number) => void;
  onDeliveryFeeChange: (amount: number) => void;
  onPaymentMethodChange: (method: string) => void;
  onOpenNewCustomerModal: () => void;
  onOpenNewDelivererModal: () => void;
};

const QuickAddButton = ({ onClick, title }: { onClick: () => void; title: string }) => (
  <button
    type="button"
    onClick={onClick}
    className="flex-shrink-0 p-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
    title={title}
  >
    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
    </svg>
  </button>
);

export function SaleCart({
  cart,
  customers,
  deliverers,
  selectedCustomerId,
  selectedDelivererId,
  subtotal,
  discount,
  deliveryFee,
  tax,
  totalAmount,
  paymentMethod,
  processing,
  taxRate,
  defaultDeliveryFee, // Receive prop
  onCustomerChange,
  onDelivererChange,
  onQuantityChange,
  onDiscountChange,
  onDeliveryFeeChange,
  onPaymentMethodChange,
  onOpenNewCustomerModal,
  onOpenNewDelivererModal,
}: Props) {
  const formatPrice = (price: number | string) => {
    const num = typeof price === 'string' ? parseFloat(price) : price;
    return (num || 0).toFixed(2);
  };

  return (
    <div className="lg:col-span-1 bg-white p-6 rounded-lg shadow-lg flex flex-col">
      <div className="flex-grow">
        <h2 className="text-3xl font-bold mb-6">Current Sale</h2>

        {/* Customer and Deliverer Selectors */}
        <div className="space-y-4 mb-6">
          <div>
            <label htmlFor="customer" className="block text-sm font-medium text-gray-700">Customer (Optional)</label>
            <div className="flex items-center gap-2 mt-1">
              <select
                id="customer"
                className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                onChange={(e) => onCustomerChange(e.target.value ? Number(e.target.value) : null)}
                value={selectedCustomerId || ""}
              >
                <option value="">None</option>
                {customers.map((customer) => (
                  <option key={customer.id} value={customer.id}>{customer.name}</option>
                ))}
              </select>
              <QuickAddButton onClick={onOpenNewCustomerModal} title="Add New Customer" />
            </div>
          </div>
          <div>
            <label htmlFor="deliverer" className="block text-sm font-medium text-gray-700">Deliverer (Optional)</label>
            <div className="flex items-center gap-2 mt-1">
              <select
                id="deliverer"
                className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                onChange={(e) => onDelivererChange(e.target.value ? Number(e.target.value) : null)}
                value={selectedDelivererId || ""}
              >
                <option value="">None</option>
                {deliverers.map((deliverer) => (
                  <option key={deliverer.id} value={deliverer.id}>{deliverer.name}</option>
                ))}
              </select>
              <QuickAddButton onClick={onOpenNewDelivererModal} title="Add New Deliverer" />
            </div>
          </div>
        </div>

        {/* Cart Items */}
        <div className="space-y-2 max-h-60 overflow-y-auto mb-4 border-t border-b py-4">
          {cart.length === 0 ? (
            <p className="text-gray-500 text-center">Cart is empty</p>
          ) : (
            cart.map(item => (
              <div key={item.product_id} className="flex items-center justify-between">
                <div>
                  <p className="font-semibold">{item.name}</p>
                  <p className="text-sm text-gray-500">${formatPrice(item.unit_price)}</p>
                </div>
                <div className="flex items-center gap-2">
                  <input
                    type="number"
                    value={item.quantity}
                    onChange={(e) => onQuantityChange(item.product_id, parseInt(e.target.value) || 0)}
                    className="w-16 p-1 border rounded-md text-center"
                  />
                  <p className="w-20 text-right font-semibold">${formatPrice(item.total)}</p>
                </div>
              </div>
            ))
          )}
        </div>

        {/* Financial Summary */}
        <div className="space-y-2">
          <div className="flex justify-between"><span>Subtotal</span><span>${subtotal.toFixed(2)}</span></div>
          <div className="flex justify-between items-center">
            <span>Discount</span>
            <input type="number" step="0.01" value={discount} onChange={e => onDiscountChange(parseFloat(e.target.value) || 0)} className="w-24 p-1 border rounded-md text-right" />
          </div>
          <div className="flex justify-between items-center">
            <span>Delivery Fee</span>
            <input type="number" step="0.01" value={deliveryFee} onChange={e => onDeliveryFeeChange(parseFloat(e.target.value) || 0)} className="w-24 p-1 border rounded-md text-right" />
          </div>
          <div className="flex justify-between"><span>Tax ({(taxRate * 100).toFixed(0)}%)</span><span>${tax.toFixed(2)}</span></div>
          <div className="font-bold text-xl border-t pt-2 mt-2 flex justify-between"><span>Total</span><span>${totalAmount.toFixed(2)}</span></div>
        </div>
      </div>

      {/* Payment and Submit */}
      <div className="mt-6">
        <div className="mt-6">
          <label htmlFor="payment" className="block text-sm font-medium text-gray-700">Payment Method</label>
          <select id="payment" onChange={e => onPaymentMethodChange(e.target.value)} value={paymentMethod} className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            <option>Cash</option>
            <option>Credit Card</option>
            <option>Bank Transfer</option>
          </select>
        </div>

        <button type="submit" disabled={processing} className="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg mt-6 hover:bg-blue-700 disabled:bg-gray-400 transition-colors text-xl">
          {processing ? 'Submitting...' : 'Submit Sale'}
        </button>
      </div>
    </div>
  );
}