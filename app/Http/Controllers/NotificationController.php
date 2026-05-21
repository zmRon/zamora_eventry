<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Get all notifications for the authenticated user, paginated
        $notifications = Auth::user()->notifications()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request)
    {
        if ($request->has('id')) {
            // Mark a specific notification as read
            $notification = Auth::user()->notifications()->find($request->id);
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            // Mark all unread notifications as read
            Auth::user()->unreadNotifications->markAsRead();
        }

        // If it's an AJAX request (e.g. from the dropdown), return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notifications updated.');
    }
}
