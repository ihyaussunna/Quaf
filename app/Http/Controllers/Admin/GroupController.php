<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Group::with('leader')
            ->withCount(['students', 'entries']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('manager_name', 'like', "%{$search}%")
                    ->orWhere('manager_contact', 'like', "%{$search}%");
            });
        }

        $groups = $query->orderBy('name')->get();
        $leaders = User::where('role', 'group_leader')->get();

        return view('admin.groups.index', compact('groups', 'search', 'leaders'));
    }

    public function create(): View
    {
        $leaders = User::where('role', 'group_leader')->get();

        return view('admin.groups.create', compact('leaders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'color_hex' => ['nullable', 'string', 'max:10'],
            'leader_id' => ['nullable', 'exists:users,id'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_contact' => ['nullable', 'string', 'max:50'],
            'name_in_results' => ['nullable', 'string', 'max:255'],
            'name_in_certificates' => ['nullable', 'string', 'max:255'],
            'admin_username' => ['nullable', 'string', 'max:255'],
            'admin_password' => ['nullable', 'string', 'max:255'],
            'assistant_managers' => ['nullable', 'array'],
        ]);

        $code = ! empty($validated['code'])
            ? Str::upper(trim($validated['code']))
            : Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 8));

        if (empty($code)) {
            $code = 'GRP'.rand(100, 999);
        }

        $baseCode = $code;
        $counter = 1;
        while (Group::where('code', $code)->exists()) {
            $code = Str::substr($baseCode, 0, 5).$counter;
            $counter++;
        }
        $validated['code'] = $code;

        $validated['color_hex'] = ! empty($validated['color_hex']) ? $validated['color_hex'] : '#2E3192';

        $slug = Str::slug($validated['name']) ?: Str::lower($code);
        $baseSlug = $slug;
        $slugCounter = 1;
        while (Group::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$slugCounter;
            $slugCounter++;
        }
        $validated['slug'] = $slug;

        $group = Group::create($validated);

        AuditLogger::log('create_group', $group, null, $group->toArray());

        return redirect()->route('admin.groups.index')->with('success', "Team '{$group->name}' created successfully.");
    }

    public function show(Group $group): View
    {
        $group->load([
            'leader',
            'students.zone',
            'entries.program.category',
            'entries.student',
        ]);

        return view('admin.groups.show', compact('group'));
    }

    public function edit(Group $group): View
    {
        $leaders = User::where('role', 'group_leader')->get();

        return view('admin.groups.edit', compact('group', 'leaders'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', "unique:groups,code,{$group->id}"],
            'color_hex' => ['required', 'string', 'max:10'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'leader_id' => ['nullable', 'exists:users,id'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_contact' => ['nullable', 'string', 'max:50'],
            'name_in_results' => ['nullable', 'string', 'max:255'],
            'name_in_certificates' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $old = $group->toArray();
        $group->update($validated);

        AuditLogger::log('update_group', $group, $old, $group->toArray());

        return redirect()->route('admin.groups.index')->with('success', "Group '{$group->name}' updated successfully.");
    }

    public function destroy(Group $group): RedirectResponse
    {
        $old = $group->toArray();
        $name = $group->name;
        $group->delete();

        AuditLogger::log('delete_group', null, $old, null);

        return redirect()->route('admin.groups.index')->with('success', "Group '{$name}' deleted.");
    }
}
