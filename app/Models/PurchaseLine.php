<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PurchaseLine extends Model
{
    use HasFactory;

    public const SOURCE_PURCHASE = 'purchase';
    public const SOURCE_OPENING_STOCK = 'opening_stock';
    public const SOURCE_ADJUSTMENT = 'adjustment';
    public const SOURCE_SALE_RETURN = 'sale_return';
    public const SOURCE_TRANSFER = 'transfer';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'purchase_price',
        'selling_price',
        'discount_amount',
        'line_total',
        'remaining_qty',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'integer',
        'selling_price' => 'integer',
        'discount_amount' => 'integer',
        'line_total' => 'integer',
        'remaining_qty' => 'integer',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($line) {
            if (is_null($line->line_total)) {
                $line->line_total = ($line->quantity * $line->purchase_price) - ($line->discount_amount ?? 0);
            }
            if (is_null($line->remaining_qty)) {
                $line->remaining_qty = $line->quantity;
            }
            if (is_null($line->source_type)) {
                $line->source_type = $line->purchase_id ? self::SOURCE_PURCHASE : self::SOURCE_OPENING_STOCK;
            }
            if (is_null($line->source_id)) {
                $line->source_id = $line->purchase_id;
            }
        });
    }

    public static function getCurrentStock(int $productId): int
    {
        return (int) self::where('product_id', $productId)
            ->sum('remaining_qty');
    }

    public static function getFIFOLayers(int $productId)
    {
        return self::where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public static function deductFIFO(int $productId, int $quantity, string $referenceNo): array
    {
        $totalCost = 0;
        $remainingToDeduct = $quantity;

        $layers = self::where('product_id', $productId)
            ->where('remaining_qty', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        if ($layers->sum('remaining_qty') < $quantity) {
            throw new \Exception("Insufficient stock for product ID: {$productId}");
        }

        foreach ($layers as $layer) {
            if ($remainingToDeduct <= 0) break;

            $deductFromLine = min($layer->remaining_qty, $remainingToDeduct);
            $cost = $deductFromLine * $layer->purchase_price;

            $layer->decrement('remaining_qty', $deductFromLine);

            $remainingToDeduct -= $deductFromLine;
            $totalCost += $cost;
        }

        StockMovement::create([
            'product_id' => $productId,
            'type' => StockMovement::TYPE_SALE,
            'quantity' => -$quantity,
            'reference_no' => $referenceNo,
            'created_by' => auth()->id(),
            'notes' => 'Sale stock deduction',
        ]);

        return [
            'total_cost' => $totalCost,
            'quantity_deducted' => $quantity - $remainingToDeduct,
        ];
    }

    public static function addStock(int $productId, int $quantity, int $unitCost, string $sourceType, ?int $sourceId = null, ?int $sellingPrice = null): self
    {
        $line = self::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'remaining_qty' => $quantity,
            'purchase_price' => $unitCost,
            'selling_price' => $sellingPrice ?? 0,
            'line_total' => $quantity * $unitCost,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        $movementType = match($sourceType) {
            self::SOURCE_OPENING_STOCK => StockMovement::TYPE_OPENING,
            self::SOURCE_ADJUSTMENT => StockMovement::TYPE_ADJUSTMENT_IN,
            self::SOURCE_SALE_RETURN => StockMovement::TYPE_SALE_RETURN,
            self::SOURCE_TRANSFER => StockMovement::TYPE_TRANSFER_IN,
            default => StockMovement::TYPE_PURCHASE,
        };

        StockMovement::create([
            'product_id' => $productId,
            'type' => $movementType,
            'quantity' => $quantity,
            'reference_no' => (string) ($sourceId ?? $line->id),
            'created_by' => auth()->id(),
            'notes' => "Stock added via {$sourceType}",
        ]);

        return $line;
    }

    public static function removeStock(int $productId, int $quantity, string $sourceType, ?int $sourceId = null): array
    {
        $remainingToRemove = $quantity;
        $totalRestored = 0;

        $layers = self::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->get();

        foreach ($layers as $layer) {
            if ($remainingToRemove <= 0) break;

            $restoreFromLine = min($layer->quantity - $layer->remaining_qty, $remainingToRemove);
            
            if ($restoreFromLine > 0) {
                $layer->increment('remaining_qty', $restoreFromLine);
                $remainingToRemove -= $restoreFromLine;
                $totalRestored += $restoreFromLine;
            }
        }

        $movementType = match($sourceType) {
            self::SOURCE_PURCHASE => StockMovement::TYPE_PURCHASE_RETURN,
            self::SOURCE_TRANSFER => StockMovement::TYPE_TRANSFER_OUT,
            default => StockMovement::TYPE_ADJUSTMENT_OUT,
        };

        StockMovement::create([
            'product_id' => $productId,
            'type' => $movementType,
            'quantity' => -$quantity,
            'reference_no' => (string) ($sourceId ?? ''),
            'created_by' => auth()->id(),
            'notes' => "Stock removal via {$sourceType}",
        ]);

        return [
            'quantity_restored' => $totalRestored,
        ];
    }
}
