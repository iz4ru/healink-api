<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    /**
     * Custom function
     */
    private function handleVoidTransaction(Transaction $transaction): void
    {
        // Muat relasi user yang membatalkan jika belum ter-load
        $transaction->loadMissing('user');
        $user = Auth::user();

        $cancelledBy = $user->name ?? 'Tidak diketahui';
        $voidReason  = $transaction->void_reason ?? '-';
        $totalAmount = number_format($transaction->total_amount, 0, ',', '.');

        $title = '❌ Transaksi Dibatalkan';
        $body  = "{$transaction->trx_no} • Rp{$totalAmount} • {$cancelledBy}: {$voidReason}";

        // Kirim ke semua Owner yang aktif
        $owners = User::where('role', ['admin', 'owner'])
            ->where('is_active', true)
            ->get();

        foreach ($owners as $owner) {
            NotificationService::sendToUser(
                $owner,
                $title,
                $body,
                'transaction_void'
            );
        }
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged('status') && $transaction->status === 'void') {
            $this->handleVoidTransaction($transaction);
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
