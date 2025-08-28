<?php
namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success']);
    }

    public function getUnreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count(),
        ]);
    }

    public function getRecent()
    {
        $notifications = auth()->user()
            ->unreadNotifications
            ->take(5)
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'message'    => $notification->data['message'],
                    'link'       => $notification->data['link'] ?? '#',
                    'type'       => $notification->data['type'] ?? 'info',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json($notifications);
    }
}
