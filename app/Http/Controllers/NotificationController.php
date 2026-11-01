<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $user = auth()->user();

        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        if (! $notification->is_read) {
            $notification->is_read = true;
            $notification->save();
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Fallback for non-AJAX requests
        return redirect()->route('notifications.index');
    }
}
