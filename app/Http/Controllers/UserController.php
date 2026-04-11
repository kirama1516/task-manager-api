<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class UserController extends Controller
{
    /**
     * 🔹 Save FCM token sent from Flutter
     */
    public function saveFcmToken(Request $request)
    {
        Log::info('📥 Incoming FCM token request', $request->all());

        $request->validate([
            'fcm_token' => 'required|string',
            'user_id'   => 'required|integer',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            Log::warning('⚠️ FCM user not found', ['user_id' => $request->user_id]);
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->update(['fcm_token' => $request->fcm_token]);

        Log::info("✅ Token saved for user {$user->id}", [
            'token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved successfully',
        ]);
    }

    /**
     * 🔹 Remove FCM token (on logout or account switch)
     */
    public function removeFcmToken(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->update(['fcm_token' => null]);
        Log::info("🗑️ Token removed for user {$user->id}");

        return response()->json(['success' => true, 'message' => 'Token removed']);
    }

    /**
     * 🔹 Send notification (Admin or API call)
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'nullable|integer',
            'role'    => 'nullable|string',
        ]);

        $sentCount = $this->sendPushNotification(
            $request->title,
            $request->message,
            $request->user_id,
            $request->role
        );

        return response()->json([
            'success' => true,
            'message' => "✅ Notification sent to {$sentCount} user(s).",
        ]);
    }

    /**
     * 🔹 Internal Firebase sender
     */
    private function sendPushNotification($title, $body, $userId = null, $role = null)
    {
        $query = User::query();

        if ($userId) {
            $query->where('id', $userId);
        } elseif ($role) {
            $query->where('role', $role);
        } else {
            $query->whereNotNull('fcm_token')->where('fcm_token', '!=', '');
        }

        $tokens = $query->pluck('fcm_token')->unique()->filter()->toArray();

        if (empty($tokens)) {
            Log::warning('⚠️ No FCM tokens found for notification', [
                'user_id' => $userId,
                'role' => $role,
            ]);
            return 0;
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.projects.app.credentials.file'));
            $messaging = $factory->createMessaging();
            $notification = FirebaseNotification::create($title, $body);

            foreach ($tokens as $token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                    ]);

                $messaging->send($message);
            }

            Log::info("✅ Sent '{$title}' to " . count($tokens) . " user(s).");
            return count($tokens);
        } catch (\Throwable $e) {
            Log::error("❌ Firebase send error: {$e->getMessage()}");
            return 0;
        }
    }
}