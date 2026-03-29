<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function storeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_info' => 'nullable|string'
        ]);

        $currentUser = Auth::user();

        $currentUser->fcmTokens()->updateOrCreate(
            ['token' => $request->token],
            ['device_info' => $request->device_info]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token FCM berhasil disimpan.'
        ], 200);
    }

    public function index(Request $request)
    {
        $paginatedNotifications = $request->user()->notifications()->latest()->paginate(10);

        $groupedNotifications = collect($paginatedNotifications->items())->groupBy(function ($notification) {
            $createdAt = Carbon::parse($notification->created_at);

            if ($createdAt->isToday()) {
                return 'Hari Ini';
            }

            if ($createdAt->isYesterday()) {
                return 'Kemarin';
            }

            if ($createdAt->greaterThanOrEqualTo(Carbon::now()->subDays(7))) {
                return '7 Hari Terakhir';
            }

            if ($createdAt->greaterThanOrEqualTo(Carbon::now()->subDays(30))) {
                return '30 Hari Terakhir';
            }

            return 'Lebih Lama';
        });

        $response = [];

        foreach ($groupedNotifications as $period => $items) {
            $formattedItems = $items->map(function ($item) use ($period) {
                $timeFormat = in_array($period, ['Hari Ini', 'Kemarin']) ? 'H:i' : 'l, H:i';

                if ($period === '30 Hari Terakhir' || $period === 'Lebih Lama') {
                    $timeFormat = 'd M Y, H:i';
                }

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'body' => $item->body,
                    'is_read' => $item->is_read,
                    'date' => Carbon::parse($item->created_at)->locale('id')->translatedFormat($timeFormat),
                ];
            });

            $response[] = [
                'period' => $period,
                'items' => $formattedItems->values(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $response,
            'meta' => [
                'current_page' => $paginatedNotifications->currentPage(),
                'last_page' => $paginatedNotifications->lastPage(),
                'has_more' => $paginatedNotifications->hasMorePages(),
            ]
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notifikasi dibaca']);
    }

    public function markMultipleAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $user = Auth::user();

        $user->notifications()
            ->whereIn('id', $request->ids)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil ditandai dibaca.',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai dibaca.',
        ]);
    }

    public function checkUnread(Request $request)
    {
        $hasUnread = $request->user()->notifications()->where('is_read', false)->exists();
        
        return response()->json([
            'status' => 'success', 
            'has_unread' => $hasUnread
        ]);
    }
}
