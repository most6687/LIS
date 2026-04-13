<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{

    public function index()
    {
        $notifications = Notification::latest()->get();

        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json([
            'message' => 'Notification created successfully',
            'data' => $notification
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted'
        ]);
    }
}
