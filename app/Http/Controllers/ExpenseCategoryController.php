<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::forUser(auth()->id())->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        ExpenseCategory::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'icon' => $request->icon ?? 'fas fa-tag',
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseCategory $category)
    {
        // Ensure user owns this category or it's a default one (if we allow editing defaults, but usually not)
        // For now, let's assume we only edit user-specific categories or copies.
        // If your logic distinguishes between system defaults and user categories, add check here.
        
        if ($category->user_id !== auth()->id() && $category->user_id !== null) {
             abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}
