<?php

namespace App\Http\Controllers;

use App\Models\PrivateLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PrivateLinkController extends Controller
{
    /**
     * Check password to grant access to private links.
     */
    public function checkPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if ($request->password === 'jawabarathebat102') {
            session(['private_access_granted' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Password salah.'], 403);
    }

    /**
     * Display the private links page.
     */
    public function index()
    {
        // Check access
        if (!session('private_access_granted')) {
            abort(404); // Hide it completely if not authenticated
        }

        $links = PrivateLink::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('private_links.index', compact('links'));
    }

    /**
     * Store a new private link.
     */
    public function store(Request $request)
    {
        if (!session('private_access_granted')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string',
        ]);

        PrivateLink::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'url' => $request->url,
            'description' => $request->description,
        ]);

        return redirect()->route('private-links.index')->with('success', 'Link berhasil disimpan.');
    }

    /**
     * Delete a private link.
     */
    public function destroy(PrivateLink $privateLink)
    {
        if (!session('private_access_granted')) {
            abort(403);
        }

        if ($privateLink->user_id !== auth()->id()) {
            abort(403);
        }

        $privateLink->delete();

        return redirect()->route('private-links.index')->with('success', 'Link berhasil dihapus.');
    }
}
