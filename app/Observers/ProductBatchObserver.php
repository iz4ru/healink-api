<?php

namespace App\Observers;

use App\Models\ProductBatch;
use App\Models\User;
use App\Services\NotificationService;

class ProductBatchObserver
{
    /**
     * Custom function
     */
    private function notifyStockToAdmins($product, $productBatch, $title, $messageSuffix): void
    {
        $admins = User::whereIn('role', ['admin', 'owner'])->get();

        foreach ($admins as $user) {
            NotificationService::sendToUser(
                $user, 
                $title, 
                "Produk {$product->product_name} (Batch: {$productBatch->batch_number}) $messageSuffix. Segera lakukan pemesanan kembali."
            );
        }
    }

    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $productBatch): void
    {
        //
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $productBatch): void
    {
        $product = $productBatch->product;
        $minStock = $product->min_stock ?? 5;

        if ($productBatch->isDirty('stock')) {
            $oldStock = $productBatch->getOriginal('stock');
            $newStock = $productBatch->stock;

            if ($oldStock > $minStock && $newStock <= $minStock && $newStock > 0) {
                $this->notifyStockToAdmins(
                    $product, 
                    $productBatch, 
                    "⚠️ Stok Mencapai Batas Minimum!", 
                    "tersisa {$newStock} unit lagi"
                );
            } else if ($oldStock > 0 && $newStock == 0) {
                $this->notifyStockToAdmins(
                    $product, 
                    $productBatch, 
                    "🚨 Stok Habis!", 
                    "sudah kosong"
                );
            }
        }
    }

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $productBatch): void
    {
        //
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $productBatch): void
    {
        //
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     */
    public function forceDeleted(ProductBatch $productBatch): void
    {
        //
    }
}
