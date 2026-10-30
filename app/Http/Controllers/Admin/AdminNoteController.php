<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNoteController extends Controller
{
    /**
     * Display the 7012450418# Notes & Pastepad Hub.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in with Admin credentials to access 7012450418# notes page.');
        }

        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized. Only admins can access 7012450418#.');
        }

        AdminNote::ensureSchema();

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $onlyPinned = (bool) $request->query('pinned', false);

        $query = AdminNote::with(['creator', 'updater']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($onlyPinned) {
            $query->where('is_pinned', true);
        }

        $notes = $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $totalCount = AdminNote::count();
        $pinnedCount = AdminNote::where('is_pinned', true)->count();
        $categories = ['general', 'announcement', 'result', 'program', 'schedule', 'urgent'];

        return view('admin.notes.index', compact(
            'notes',
            'search',
            'category',
            'onlyPinned',
            'totalCount',
            'pinnedCount',
            'categories'
        ));
    }

    /**
     * Store a new note or pasted text snippet.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $title = ! empty($validated['title']) ? trim($validated['title']) : null;
        if (! $title) {
            // Auto-generate title from first line of content if not provided
            $firstLine = trim(strtok($validated['content'], "\r\n") ?: '');
            $title = mb_substr($firstLine, 0, 60) ?: 'Note #'.(AdminNote::count() + 1);
        }

        $note = AdminNote::create([
            'title' => $title,
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'general',
            'color' => $validated['color'] ?? 'slate',
            'is_pinned' => $request->boolean('is_pinned', false),
            'created_by_user_id' => auth()->id(),
            'updated_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note saved successfully to 7012450418#',
                'note' => $note,
            ]);
        }

        return back()->with('success', 'Note saved successfully to 7012450418#');
    }

    /**
     * Update an existing note.
     */
    public function update(Request $request, AdminNote $note): RedirectResponse|JsonResponse
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $note->update([
            'title' => ! empty($validated['title']) ? trim($validated['title']) : $note->title,
            'content' => $validated['content'],
            'category' => $validated['category'] ?? $note->category,
            'color' => $validated['color'] ?? $note->color,
            'is_pinned' => $request->has('is_pinned') ? $request->boolean('is_pinned') : $note->is_pinned,
            'updated_by_user_id' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully.',
                'note' => $note,
            ]);
        }

        return back()->with('success', 'Note updated successfully.');
    }

    /**
     * Delete a note.
     */
    public function destroy(AdminNote $note): RedirectResponse|JsonResponse
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $note->delete();

        return back()->with('success', 'Note deleted successfully.');
    }

    /**
     * Toggle pinned status.
     */
    public function togglePin(AdminNote $note): RedirectResponse|JsonResponse
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $note->update([
            'is_pinned' => ! $note->is_pinned,
            'updated_by_user_id' => auth()->id(),
        ]);

        $statusMsg = $note->is_pinned ? 'Note pinned to top.' : 'Note unpinned.';

        return back()->with('success', $statusMsg);
    }
}
