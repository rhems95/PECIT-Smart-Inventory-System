<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with('creator')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'priority' => ['nullable', 'in:low,normal,high'],
        ]);

        $announcement = Announcement::create($data + [
            'created_by' => auth()->id(),
            'is_active' => true,
            'published_at' => now(),
            'priority' => $data['priority'] ?? 'normal',
        ]);

        $auditLog->log($request->user(), 'announcement.created', $announcement, null, [
            'title' => $announcement->title,
            'priority' => $announcement->priority,
        ]);

        return back()->with('success', 'Announcement published.');
    }
}
