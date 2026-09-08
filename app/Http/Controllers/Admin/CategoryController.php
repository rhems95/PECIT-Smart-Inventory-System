<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::latest()->paginate(15)]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($data + ['slug' => str()->slug($data['name'])]);
        $auditLog->log($request->user(), 'category.created', $category, null, ['name' => $category->name]);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $before = $category->only(['name', 'is_active']);
        $category->update($data + ['slug' => str()->slug($data['name'])]);
        $auditLog->log($request->user(), 'category.updated', $category, $before, ['name' => $category->name]);

        return back()->with('success', 'Category updated.');
    }
}
