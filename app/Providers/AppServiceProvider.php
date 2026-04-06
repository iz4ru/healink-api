<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Transaction;
use App\Observers\ProductBatchObserver;
use App\Observers\ProductObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductBatch::observe(ProductBatchObserver::class);
        Transaction::observe(TransactionObserver::class);
        Product::observe(ProductObserver::class);
    }
}
