<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseLine;
use App\Models\InventoryLayer;
use Illuminate\Console\Command;

class RebuildStock extends Command
{
    protected $signature = 'stock:rebuild {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Rebuild product stock from purchase_lines.remaining_qty';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting stock rebuild from purchase_lines...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $products = Product::all();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $updates = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                $purchaseLineStock = PurchaseLine::where('product_id', $product->id)
                    ->sum('remaining_qty');

                $currentStock = $product->stock ?? 0;
                $calculatedStock = (int) $purchaseLineStock;

                if (!$dryRun) {
                    $product->stock = $calculatedStock;
                    $product->save();

                    ProductStock::updateOrCreate(
                        ['product_id' => $product->id],
                        ['current_stock' => $calculatedStock]
                    );

                    InventoryLayer::where('product_id', $product->id)
                        ->where('source_type', 'purchase')
                        ->delete();

                    $purchaseLines = PurchaseLine::where('product_id', $product->id)->get();
                    foreach ($purchaseLines as $line) {
                        InventoryLayer::create([
                            'product_id' => $product->id,
                            'source_type' => 'purchase',
                            'source_id' => $line->purchase_id,
                            'quantity' => $line->remaining_qty,
                            'remaining_quantity' => $line->remaining_qty,
                            'unit_cost' => $line->purchase_price,
                        ]);
                    }
                }

                if ($currentStock !== $calculatedStock) {
                    $updates++;
                    if ($this->option('verbose')) {
                        $this->line("Product ID {$product->id} ({$product->name}): {$currentStock} -> {$calculatedStock}");
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error for product {$product->name}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Stock rebuild complete!");
        $this->info("Products checked: {$products->count()}");
        $this->info("Products updated: {$updates}");
        
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
