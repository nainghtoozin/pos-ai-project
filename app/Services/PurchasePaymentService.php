<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;

class PurchasePaymentService
{
    public function createPayment(Purchase $purchase, array $data): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $data) {
            $amount = (int) $data['amount'];

            if ($amount > $purchase->due_amount) {
                throw new \InvalidArgumentException('Payment amount cannot exceed due amount.');
            }

            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $purchase->paid_amount = $purchase->paid_amount + $amount;
            $purchase->due_amount = $purchase->due_amount - $amount;
            $purchase->payment_status = $this->calculatePaymentStatus($purchase->paid_amount, $purchase->total_amount);
            $purchase->save();

            return $payment;
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
