<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        // Redirect to the notification's action URL if available, or back
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return back();
    }
}
