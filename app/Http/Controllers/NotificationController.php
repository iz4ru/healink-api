<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/private/firebase_credentials.json'));
        $messaging = $factory->createMessaging();

        $deviceToken = $request->input('device_token');
        $title = $request->input('title');
        $body = $request->input('body');

        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'android' => [
                'priority' => 'high', 
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'icon' => 'notification_icon',
                    'color' => '#3A7CF0',
                ],
            ],
        ]);

        $messaging->send($message);

        return response()->json(['status' => 'Pesan terkirim ke perangkat']);
    }
}
