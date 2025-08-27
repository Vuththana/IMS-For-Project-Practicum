<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Inventory\Batch;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\SubCategory;
use App\Models\Partner\Customer;
use App\Models\Partner\Deliverer;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleItem;
use App\Models\Sale\SaleItemBatch;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CashRegisterController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->current_company_id) {
            // Redirect if the user or their company isn't set
            return redirect('/login');
        }

        $companyId = $user->current_company_id; // Using current_company_id as in your example
        $company = Company::find($companyId);
        $companyName = $company ? $company->name : 'Your Company';

        $products = Product::where('company_id', $companyId)->with(['batches', 'category'])->get();
        $customers = Customer::where('company_id', $companyId)->get();
        $deliverers = Deliverer::where('company_id', $companyId)->get();
        $categories = Category::where('company_id', $companyId)->get();
        $subcategories = SubCategory::where('company_id', $companyId)->get();
        $brands = Brand::where('company_id', $companyId)->get();

        // 4. Render the Inertia page and pass ALL data as props
        return Inertia::render('Index', [
            'products' => $products,
            'customers' => $customers,
            'deliverers' => $deliverers,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'brands' => $brands,
            'currentCompanyId' => $companyId,
            'companyName' => $companyName,
            'taxRate' => Setting::getTaxRate($companyId),
            'defaultDeliveryFee' => Setting::getDefaultDeliveryFee($companyId),
        ]);
    }

    public function store(Request $request)
    {
        // 1. --- VALIDATE THE INCOMING DATA ---
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'deliverer_id' => 'nullable|exists:deliverers,id',
            'payment_method' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.selected_batches' => 'required|array',
            'items.*.selected_batches.*.batch_id' => 'required|exists:batches,id',
            'items.*.selected_batches.*.quantity' => 'required|integer|min:1',
        ]);

        // 2. --- START DATABASE TRANSACTION ---
        // This ensures that if any step fails, all changes are rolled back.
        DB::beginTransaction();

        try {
            // 3. --- CREATE THE MAIN SALE RECORD ---
            $sale = Sale::create([
                'company_id' => auth()->user()->current_company_id,
                'customer_id' => $request->customer_id,
                'deliverer_id' => $request->deliverer_id,
                'invoice_number' => 'INV-' . str_pad((Sale::max('id') + 1), 4, '0', STR_PAD_LEFT), // Generate a random invoice number
                'sale_date' => Carbon::now(),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'delivery_status' => $request->deliverer_id ? 'pending' : 'delivered', // Or based on your logic
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'delivery_fee' => $request->delivery_fee,
                'tax' => $request->tax,
                'total_amount' => $request->total_amount,
                'created_by' => auth()->id(),

            ]);

            // 4. --- LOOP THROUGH CART ITEMS ---
            foreach ($request->items as $itemData) {
                // Find the product to get its details
                $product = Product::find($itemData['product_id']);
                $totalCogs = 0;

                // 5. --- CREATE THE SALE ITEM RECORD ---
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['total'],
                    // COGS will be calculated below
                ]);

                // 6. --- LOOP THROUGH BATCHES FOR THIS ITEM ---
                foreach ($itemData['selected_batches'] as $batchData) {
                    $batch = Batch::find($batchData['batch_id']);

                    // Safety check: ensure we don't sell more than is available
                    if ($batch->remaining_quantity < $batchData['quantity']) {
                        throw new \Exception("Not enough stock in batch {$batch->batch_number} for product {$product->name}.");
                    }

                    // 7. --- CREATE THE SALE ITEM BATCH RECORD ---
                    SaleItemBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'batch_id' => $batch->id,
                        'quantity' => $batchData['quantity'],
                        'cost_price' => $batch->cost_price,
                    ]);

                    // 8. --- DECREMENT INVENTORY ---
                    // This is the most critical step for inventory management.
                    $batch->decrement('remaining_quantity', $batchData['quantity']);

                    // Calculate COGS for this part of the sale item
                    $totalCogs += $batchData['quantity'] * $batch->cost_price;
                }

                // Update the SaleItem with the calculated Cost of Goods Sold
                $saleItem->update(['cogs' => $totalCogs]);
            }

            // 9. --- COMMIT THE TRANSACTION ---
            // If we get here, everything was successful.
            DB::commit();

            return back()->with('success', 'Sale created successfully!');

        } catch (\Exception $e) {
            // 10. --- ROLLBACK ON FAILURE ---
            // If any error occurred, undo all database changes.
            DB::rollBack();

            // Log the error and return with an error message
            \Log::error('Sale creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'There was a problem creating the sale. Please try again. ' . $e->getMessage()]);
        }
    }
}
