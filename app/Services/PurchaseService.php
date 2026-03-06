<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $subtotal = $this->calculateSubtotal($data['lines']);
            $discountAmount = $this->calculateDiscount($subtotal, $data['discount_type'] ?? 'none', $data['discount_amount'] ?? 0);
            $taxAmount = $data['tax_amount'] ?? 0;
            $shippingCharges = $data['shipping_charges'] ?? 0;
            $otherCharges = $data['other_charges'] ?? 0;
            
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCharges + $otherCharges;
            $paidAmount = $data['paid_amount'] ?? 0;
            $dueAmount = $totalAmount - $paidAmount;
            
            $paymentStatus = $this->calculatePaymentStatus($paidAmount, $totalAmount);

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'status' => $data['status'],
                'discount_type' => $data['discount_type'] ?? 'none',
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_charges' => $shippingCharges,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->createPurchaseLines($purchase, $data['lines']);

            if ($data['status'] === Purchase::STATUS_RECEIVED) {
                $this->updateStockAndCreateMovements($purchase);
            }

            return $purchase->load(['supplier', 'lines.product', 'creator']);
        });
    }

    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            $oldStatus = $purchase->status;
            $newStatus = $data['status'];

            $subtotal = $this->calculateSubtotal($data['lines']);
            $discountAmount = $this->calculateDiscount($subtotal, $data['discount_type'] ?? 'none', $data['discount_amount'] ?? 0);
            $taxAmount = $data['tax_amount'] ?? 0;
            $shippingCharges = $data['shipping_charges'] ?? 0;
            $otherCharges = $data['other_charges'] ?? 0;
            
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCharges + $otherCharges;
            $paidAmount = $data['paid_amount'] ?? 0;
            $dueAmount = $totalAmount - $paidAmount;
            
            $paymentStatus = $this->calculatePaymentStatus($paidAmount, $totalAmount);

            $purchase->update([
                'supplier_id' => $data['supplier_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'status' => $newStatus,
                'discount_type' => $data['discount_type'] ?? 'none',
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_charges' => $shippingCharges,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->reverseStockAndMovements($purchase);
            $purchase->lines()->delete();
            $this->createPurchaseLines($purchase, $data['lines']);

            if ($newStatus === Purchase::STATUS_RECEIVED) {
                $this->updateStockAndCreateMovements($purchase);
            }

            return $purchase->load(['supplier', 'lines.product', 'creator']);
        });
    }

    protected function calculateSubtotal(array $lines): float
    {
        return collect($lines)->sum(function ($line) {
            $quantity = $line['quantity'];
            $purchasePrice = $line['purchase_price'];
            $discountAmount = $line['discount_amount'] ?? 0;
            return ($quantity * $purchasePrice) - $discountAmount;
        });
    }

    protected function calculateDiscount(float $subtotal, string $discountType, float $discountAmount): float
    {
        return match ($discountType) {
            'fixed' => $discountAmount,
            'percentage' => $subtotal * ($discountAmount / 100),
            default => 0,
        };
    }

    protected function calculatePaymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount <= 0) {
            return Purchase::PAYMENT_STATUS_DUE;
        } elseif ($paidAmount >= $totalAmount) {
            return Purchase::PAYMENT_STATUS_PAID;
        }
        return Purchase::PAYMENT_STATUS_PARTIAL;
    }

    protected function createPurchaseLines(Purchase $purchase, array $lines): void
    {
        foreach ($lines as $line) {
            $quantity = $line['quantity'];
            $purchasePrice = $line['purchase_price'];
            $discountAmount = $line['discount_amount'] ?? 0;
            $lineTotal = ($quantity * $purchasePrice) - $discountAmount;
            $sellingPrice = $line['selling_price'] ?? null;

            PurchaseLine::create([
                'purchase_id' => $purchase->id,
                'product_id' => $line['product_id'],
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice ?? 0,
                'discount_amount' => $discountAmount,
                'line_total' => $lineTotal,
                'source_type' => 'purchase',
                'source_id' => $purchase->id,
            ]);

            if ($sellingPrice !== null) {
                $this->updateProductSellingPrice($line['product_id'], $sellingPrice);
            }
        }
    }

    protected function updateProductSellingPrice(int $productId, float $sellingPrice): void
    {
        Product::where('id', $productId)->update(['sale_price' => $sellingPrice]);
    }

    protected function updateStockAndCreateMovements(Purchase $purchase): void
    {
        foreach ($purchase->lines as $line) {
            PurchaseLine::addStock(
                $line->product_id,
                $line->quantity,
                $line->purchase_price,
                PurchaseLine::SOURCE_PURCHASE,
                $purchase->id,
                $line->selling_price
            );
        }
    }

    protected function reverseStockAndMovements(Purchase $purchase): void
    {
        foreach ($purchase->lines as $line) {
            $line->remaining_qty = 0;
            $line->save();
        }

        StockMovement::where('reference_no', (string) $purchase->id)
            ->whereIn('type', [
                StockMovement::TYPE_PURCHASE,
                StockMovement::TYPE_OPENING,
                StockMovement::TYPE_ADJUSTMENT_IN,
            ])
            ->delete();
    }

    public function calculatePurchaseTotals(array $lines, array $discountData): array
    {
        $subtotal = $this->calculateSubtotal($lines);
        $discountAmount = $this->calculateDiscount(
            $subtotal, 
            $discountData['type'] ?? 'none', 
            $discountData['amount'] ?? 0
        );
        
        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
        ];
    }
}
