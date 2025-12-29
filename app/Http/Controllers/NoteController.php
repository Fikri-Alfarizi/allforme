<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Display all notes.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $tag = $request->get('tag');

        $query = $user->notes();

        // Search
        if ($search) {
            $query->search($search);
        }

        // Filter by tag
        if ($tag) {
            $query->withTag($tag);
        }

        // Order: pinned first, then by updated date
        $notes = $query->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // Get all unique tags
        $allTags = $user->notes()
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('notes.index', compact('notes', 'allTags', 'search', 'tag'));
    }

    /**
     * Show form to create new note.
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store new note.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'color' => 'nullable|string|max:7',
            'is_pinned' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Note::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'tags' => $request->tags,
            'color' => $request->color,
            'is_pinned' => $request->is_pinned ?? false,
        ]);

        return redirect()->route('notes.index')
            ->with('success', 'Catatan berhasil dibuat!');
    }

    /**
     * Show note details.
     */
    public function show(Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        return view('notes.show', compact('note'));
    }

    /**
     * Show form to edit note.
     */
    public function edit(Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        return view('notes.edit', compact('note'));
    }

    /**
     * Update note.
     */
    public function update(Request $request, Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'color' => 'nullable|string|max:7',
            'is_pinned' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $note->update([
            'title' => $request->title,
            'content' => $request->content,
            'tags' => $request->tags,
            'color' => $request->color,
            'is_pinned' => $request->is_pinned ?? false,
        ]);

        return redirect()->route('notes.index')
            ->with('success', 'Catatan berhasil diupdate!');
    }

    /**
     * Delete note (soft delete).
     */
    public function destroy(Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Catatan berhasil dihapus!');
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->togglePin();

        return back()->with('success', $note->is_pinned ? 'Catatan di-pin!' : 'Pin dihapus!');
    }

    /**
     * Add tag to note.
     */
    public function addTag(Request $request, Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'tag' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $note->addTag($request->tag);

        return back()->with('success', 'Tag berhasil ditambahkan!');
    }

    /**
     * Remove tag from note.
     */
    public function removeTag(Request $request, Note $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'tag' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $note->removeTag($request->tag);

        return back()->with('success', 'Tag berhasil dihapus!');
    }

    /**
     * Show trash (soft deleted notes).
     */
    public function trash()
    {
        $notes = auth()->user()->notes()->onlyTrashed()->paginate(20);

        return view('notes.trash', compact('notes'));
    }

    /**
     * Restore note from trash.
     */
    public function restore($id)
    {
        $note = Note::onlyTrashed()->findOrFail($id);

        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->restore();

        return back()->with('success', 'Catatan berhasil dipulihkan!');
    }

    /**
     * Permanently delete note.
     */
    public function forceDelete($id)
    {
        $note = Note::onlyTrashed()->findOrFail($id);

        // Ensure user owns this note
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->forceDelete();

        return back()->with('success', 'Catatan berhasil dihapus permanen!');
    }
}
