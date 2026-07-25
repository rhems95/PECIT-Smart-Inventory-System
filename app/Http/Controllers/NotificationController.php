<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->psisNotifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(int $notification): RedirectResponse
    {
        $record = auth()->user()->psisNotifications()->findOrFail($notification);
        $record->update(['is_read' => true]);

        if ($record->link) {
            return redirect($record->link);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->psisNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
