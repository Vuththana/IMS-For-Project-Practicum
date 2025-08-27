<?php

namespace App\Livewire;

use App\Models\Inventory\Product;
use App\Models\Sale\Sale;
use Filament\Notifications\Notification;
use Livewire\Component;

class PosSystem extends Component
{
    public $cart = [];
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $paymentMethod = 'cash';
    public $search = '';
    public $processingPayment = false;

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);
        
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'total' => $product->price
            ];
        }
        
        $this->updateCartTotal($productId);
    }

    public function incrementQuantity($productId)
    {
        $this->cart[$productId]['quantity']++;
        $this->updateCartTotal($productId);
    }

    public function decrementQuantity($productId)
    {
        if ($this->cart[$productId]['quantity'] > 1) {
            $this->cart[$productId]['quantity']--;
            $this->updateCartTotal($productId);
        }
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
    }

    private function updateCartTotal($productId)
    {
        $this->cart[$productId]['total'] = 
            $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
    }

    public function getProductsProperty()
    {
        return Product::when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->get();
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function getTotalAmountProperty()
    {
        return $this->subtotal + ($this->subtotal * ($this->taxRate / 100)) - $this->discount;
    }

    public function processPayment()
    {
        $this->validate([
            'customerName' => 'required',
            'customerPhone' => 'required',
            'cart' => 'required|array|min:1'
        ]);

        try {
            $this->processingPayment = true;
            
            // Create sale
            $sale = Sale::create([
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'payment_method' => $this->paymentMethod,
                'total_amount' => $this->totalAmount,
            ]);

            // Create sale items
            foreach ($this->cart as $item) {
                $sale->items()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['total']
                ]);
            }

            $this->reset();
            $this->processingPayment = false;
            
            Notification::make()
                ->title('Sale completed successfully')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            $this->processingPayment = false;
            Notification::make()
                ->title('Error processing payment: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.pos-system');
    }
}
