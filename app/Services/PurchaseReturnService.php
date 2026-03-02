<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function createReturn(Purchase $purchase, array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchase, $data) {
            $totalReturnAmount = 0;

            $return = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_date' => $data['return_date'],
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $quantity = (int) $item['quantity'];
                $returnPrice = (int) $item['return_price'];

                $purchasedQty = $purchase->lines()
                    ->where('product_id', $productId)
                    ->sum('quantity');

                $alreadyReturned = $return->whereHas('items', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })->sum('quantity');

                $availableToReturn = $purchasedQty - $alreadyReturned;

                if ($quantity > $availableToReturn) {
                    throw new \InvalidArgumentException("Cannot return more than purchased quantity for product ID {$productId}");
                }

                $subtotal = $quantity * $returnPrice;
                $totalReturnAmount += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'return_price' => $returnPrice,
                    'subtotal' => $subtotal,
                ]);

                $stock = ProductStock::where('product_id', $productId)->first();
                if ($stock && $stock->current_stock >= $quantity) {
                    $stock->current_stock -= $quantity;
                    $stock->save();

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => StockMovement::TYPE_PURCHASE_RETURN,
                        'quantity' => $quantity,
                        'reference_no' => $return->id,
                        'created_by' => auth()->id(),
                        'notes' => 'Purchase Return #' . $return->id,
                    ]);
                }
            }

            $return->total_return_amount = $totalReturnAmount;
            $return->save();

            $purchase->total_amount = $purchase->total_amount - $totalReturnAmount;
            $purchase->due_amount = $purchase->due_amount - $totalReturnAmount;
            $purchase->payment_status = $this->calculatePaymentStatus($purchase->paid_amount, $purchase->total_amount);
            $purchase->save();

            return $return;
        });
    }

    private function calculatePaymentStatus(int $paidAmount, int $totalAmount): string
    {
        if ($paidAmount <= 0) {
            return Purchase::PAYMENT_STATUS_DUE;
        } elseif ($paidAmount >= $totalAmount) {
            return Purchase::PAYMENT_STATUS_PAID;
        }
        return Purchase::PAYMENT_STATUS_PARTIAL;
    }
}
