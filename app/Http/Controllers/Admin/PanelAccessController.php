<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PanelAccessController extends Controller
{
    /**
     * Display a listing of all festival panels and access credentials.
     */
    public function index(Request $request): View
    {
        $query = User::query()->with(['ledGroup', 'judge']);

        // Search filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Panel/Role filter
        if ($panel = $request->input('panel')) {
            if ($panel === 'admin') {
                $query->whereIn('role', ['super_admin', 'admin']);
            } elseif ($panel === 'samithi') {
                $query->whereIn('role', ['program_committee', 'program_coordinator']);
            } elseif ($panel === 'leader') {
                $query->where('role', 'group_leader');
            } elseif ($panel === 'judge') {
                $query->where('role', 'judge');
            } elseif ($panel === 'media') {
                $query->whereIn('role', ['media_team', 'media_manager']);
            } elseif ($panel === 'announcer') {
                $query->where('role', 'announcer');
            } elseif ($panel === 'greenroom') {
                $query->where('role', 'green_room_coordinator');
            } elseif ($panel === 'student') {
                $query->where('role', 'student');
            }
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'locked') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderByRaw("
            CASE 
                WHEN role = 'super_admin' THEN 1
                WHEN role = 'admin' THEN 2
                WHEN role IN ('program_committee', 'program_coordinator') THEN 3
                WHEN role = 'announcer' THEN 4
                WHEN role IN ('media_team', 'media_manager') THEN 5
                WHEN role = 'group_leader' THEN 6
                WHEN role = 'judge' THEN 7
                WHEN role = 'green_room_coordinator' THEN 8
                ELSE 9
            END ASC
        ")->orderBy('id')->get();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'locked' => User::where('is_active', false)->count(),
            'leaders' => User::where('role', 'group_leader')->count(),
            'judges' => User::where('role', 'judge')->count(),
            'media' => User::whereIn('role', ['media_team', 'media_manager'])->count(),
            'announcer' => User::where('role', 'announcer')->count(),
        ];

        $groups = Group::with('leader')->orderBy('name')->get();

        return view('admin.panel-access.index', compact('users', 'stats', 'groups'));
    }

    /**
     * Toggle account status (Lock / Activate).
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'സ്വന്തം അഡ്മിൻ അക്കൗണ്ട് ലോക്ക് ചെയ്യാൻ സാധ്യമല്ല.');
        }

        $oldStatus = $user->is_active;
        $user->is_active = ! $oldStatus;
        $user->save();

        $actionName = $user->is_active ? 'അൺലോക്ക് ചെയ്തു (Activated)' : 'ലോക്ക് ചെയ്തു (Locked)';

        AuditLogger::log(
            $user->is_active ? 'unlock_user_account' : 'lock_user_account',
            $user,
            ['is_active' => $oldStatus],
            ['is_active' => $user->is_active]
        );

        return back()->with('success', "{$user->name} അക്കൗണ്ട് വിജയകരമായി {$actionName}.");
    }

    /**
     * Update user password and plain_password.
     */
    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $newPassword = $validated['password'];

        $user->password = Hash::make($newPassword);
        $user->plain_password = $newPassword;
        $user->save();

        // If user is group leader, sync group admin_password
        if ($user->role === 'group_leader') {
            Group::where('leader_id', $user->id)->update([
                'admin_password' => $newPassword,
            ]);
        }

        AuditLogger::log(
            'update_panel_password',
            $user,
            null,
            ['updated_by' => Auth::id()]
        );

        return back()->with('success', "{$user->name} അക്കൗണ്ടിന്റെ പാസ്‌വേഡ് അപ്‌ഡേറ്റ് ചെയ്തു.");
    }
}
