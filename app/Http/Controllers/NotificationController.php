<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request)
    {
        $notifications = InAppNotification::forUser($request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    // GET /api/notifications/unread
    public function unread(Request $request)
    {
        $notifications = InAppNotification::forUser($request->user()->id)
            ->unread()
            ->latest()
            ->get();

        return response()->json($notifications);
    }

    // POST /api/notifications/{id}/read
    public function markAsRead(Request $request, $id)
    {
        $notification = InAppNotification::forUser($request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Marked as read'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        InAppNotification::forUser($request->user()->id)
            ->unread()
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}