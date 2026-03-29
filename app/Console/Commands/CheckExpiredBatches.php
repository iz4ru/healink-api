<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ProductBatch;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batches:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi peringatan batch produk yang mendekati atau sudah expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil semua Admin & Owner
        $recipients = User::whereIn('role', ['admin', 'owner'])
            ->where('is_active', true)
            ->get();

        if ($recipients->isEmpty()) {
            $this->info('Tidak ada penerima notifikasi.');
            return Command::SUCCESS;
        }

        $today = Carbon::today();
        $in7Days = $today->copy()->addDays(7);
        $in30Days = $today->copy()->addDays(30);

        // Ambil batch yang relevan sekaligus — 1 query
        $batches = ProductBatch::with('product')
            ->whereNull('deleted_at')
            ->where('stock', '>', 0) // Hanya yang masih ada stoknya
            ->where(function ($query) use ($today, $in30Days) {
                $query->whereDate('exp_date', '<', $today)         // Sudah expired
                      ->orWhereDate('exp_date', $today)            // Expired hari ini
                      ->orWhereBetween('exp_date', [               // H-7 dan H-30
                            $today->copy()->addDay(),
                            $in30Days,
                        ]);
            })
            ->get();

        if ($batches->isEmpty()) {
            $this->info('Tidak ada batch yang perlu diperingatkan.');
            return Command::SUCCESS;
        }

        $notifCount = 0;

        foreach ($batches as $batch) {
            $expDate = Carbon::parse($batch->exp_date);
            $daysLeft = $today->diffInDays($expDate, false); // false = bisa negatif
            $productName = $batch->product->product_name ?? 'Produk tidak diketahui';
            $batchNumber = $batch->batch_number ?? 'NO-BATCH';

            // Tentukan tipe & pesan berdasarkan sisa hari
            [$type, $title, $body] = $this->resolveMessage($productName, $batchNumber, $daysLeft, $expDate);

            // Hindari duplikat — jangan kirim notif yang sama di hari yang sama
            foreach ($recipients as $user) {
                $alreadySent = Notification::where('user_id', $user->id)
                    ->where('type', $type)
                    ->where('body', 'like', "%$batchNumber%")
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($alreadySent) continue;

                NotificationService::sendToUser($user, $title, $body, $type);

                $notifCount++;
            }
        }

        $this->info("$notifCount notifikasi berhasil dikirim.");
        return Command::SUCCESS;
    }

    private function resolveMessage(
        string $productName,
        string $batchNumber,
        int $daysLeft,
        Carbon $expDate
    ): array {
        $formattedDate = $expDate->translatedFormat('d F Y');

        if ($daysLeft < 0) {
            // Sudah kadaluarsa
            return [
                'batch_expired',
                '[📦] 🚨 Produk Sudah Kadaluarsa!',
                "Produk $productName " . " (Batch: $batchNumber) " . " telah melewati tanggal expired pada $formattedDate. Segera tarik dari display!",
            ];
        }

        if ($daysLeft === 0) {
            // Kadaluarsa hari ini
            return [
                'batch_expired',
                '[📦] ❗ Produk Kadaluarsa Hari Ini!',
                "Produk $productName (Batch: $batchNumber) kadaluarsa hari ini ($formattedDate). Segera tarik dari display!",
            ];
        }

        if ($daysLeft === 1) {
            // Kadaluarsa besok
            return [
                'batch_expiring_tomorrow',
                '[📦] ⚠️ Produk Kadaluarsa Besok!',
                "Produk $productName (Batch: $batchNumber) kadaluarsa besok ($formattedDate). Pertimbangkan untuk segera menjualnya.",
            ];
        }

        if ($daysLeft <= 7) {
            // H-7
            return [
                'batch_expiring_soon_7',
                '[📦] 🕒 Peringatan Kadaluarsa 7 Hari',
                "Produk $productName (Batch: $batchNumber) akan kadaluarsa pada $formattedDate ($daysLeft hari lagi). Segera tindak lanjuti.",
            ];
        }

        // H-30
        return [
            'batch_expiring_soon_30',
            '[📦] 🕒 Peringatan Kadaluarsa 30 Hari',
            "Produk $productName (Batch: $batchNumber) akan kadaluarsa pada $formattedDate ($daysLeft hari lagi).",
        ];
    }
}
