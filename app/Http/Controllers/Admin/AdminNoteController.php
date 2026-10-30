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
     * Standalone Public View for 7012450418# (No login required, completely isolated).
     */
    public function publicIndex(Request $request): View
    {
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
            ->paginate(50)
            ->withQueryString();

        $totalCount = AdminNote::count();
        $pinnedCount = AdminNote::where('is_pinned', true)->count();
        $categories = ['general', 'announcement', 'result', 'program', 'schedule', 'urgent'];

        return view('standalone-notes.7012450418', compact(
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
     * Public Store for 7012450418# (No login required).
     */
    public function publicStore(Request $request): RedirectResponse|JsonResponse
    {
        AdminNote::ensureSchema();

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $title = ! empty($validated['title']) ? trim($validated['title']) : null;
        if (! $title) {
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

        return redirect('/7012450418')->with('success', 'Note saved successfully to 7012450418#');
    }

    /**
     * Public Update for 7012450418# (No login required).
     */
    public function publicUpdate(Request $request, AdminNote $note): RedirectResponse|JsonResponse
    {
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

        return redirect('/7012450418')->with('success', 'Note updated successfully.');
    }

    /**
     * Public Destroy for 7012450418# (No login required).
     */
    public function publicDestroy(AdminNote $note): RedirectResponse|JsonResponse
    {
        $note->delete();

        return redirect('/7012450418')->with('success', 'Note deleted successfully.');
    }

    /**
     * Public Toggle Pin for 7012450418# (No login required).
     */
    public function publicTogglePin(AdminNote $note): RedirectResponse|JsonResponse
    {
        $note->update([
            'is_pinned' => ! $note->is_pinned,
            'updated_by_user_id' => auth()->id(),
        ]);

        $statusMsg = $note->is_pinned ? 'Note pinned to top.' : 'Note unpinned.';

        return redirect('/7012450418')->with('success', $statusMsg);
    }

    /**
     * Admin panel index view.
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
     * Store a new note from admin panel.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        return $this->publicStore($request);
    }

    /**
     * Update an existing note from admin panel.
     */
    public function update(Request $request, AdminNote $note): RedirectResponse|JsonResponse
    {
        return $this->publicUpdate($request, $note);
    }

    /**
     * Delete a note from admin panel.
     */
    public function destroy(AdminNote $note): RedirectResponse|JsonResponse
    {
        return $this->publicDestroy($note);
    }

    /**
     * Toggle pinned status from admin panel.
     */
    public function togglePin(AdminNote $note): RedirectResponse|JsonResponse
    {
        return $this->publicTogglePin($note);
    }
}
