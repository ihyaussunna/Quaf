<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->has('switch') || $request->has('logout')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return view('auth.login');
        }

        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $login = trim($request->input('username') ?? $request->input('email') ?? '');
        $password = (string) $request->input('password');

        if (empty($login) || empty($password)) {
            return back()->withErrors([
                'username' => 'Please enter both username and password.',
            ])->withInput();
        }

        $remember = $request->boolean('remember', true);

        $authenticated = false;
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $authenticated = Auth::attempt(['email' => $login, 'password' => $password], $remember);
        }

        if (! $authenticated) {
            $user = User::where('email', $login)
                ->orWhere('name', $login)
                ->orWhere('phone', $login)
                ->orWhere('email', 'like', $login.'@%')
                ->orWhere('email', 'like', 'leader.'.$login.'@%')
                ->first();

            if (! $user) {
                // Match by group code or group name for leaders
                $cleanLogin = strtolower(trim($login));
                $group = Group::whereRaw('LOWER(code) = ?', [$cleanLogin])
                    ->orWhereRaw('LOWER(name) = ?', [$cleanLogin])
                    ->orWhere('name', 'like', "%{$login}%")
                    ->first();

                if ($group && $group->leader_id) {
                    $user = User::find($group->leader_id);
                }
            }

            if (! $user && in_array(strtolower(trim($login)), ['samithi', 'program', 'program_committee', 'program_coordinator'])) {
                $user = User::whereIn('role', ['program_committee', 'program_coordinator'])->first();
            }

            if (! $user && in_array(strtolower(trim($login)), ['media', 'media_team', 'media_manager', 'press'])) {
                $user = User::whereIn('role', ['media_team', 'media_manager'])->first();
            }

            if (! $user && in_array(strtolower(trim($login)), ['announcer', 'announcement', 'desk', 'mike'])) {
                $user = User::where('role', 'announcer')->first();
            }

            if (! $user) {
                $user = User::where('name', 'like', "%{$login}%")->first();
            }

            if ($user && Hash::check($password, $user->password)) {
                Auth::login($user, $remember);
                $authenticated = true;
            }
        }

        if ($authenticated) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->is_active === false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['username' => 'Locked: ഈ അക്കൗണ്ട് അഡ്മിൻ ലോക്ക് ചെയ്തിരിക്കുന്നു. ലോഗിൻ ചെയ്യാൻ സാധ്യമല്ല (Account Locked).']);
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our festival records.',
        ])->onlyInput('username', 'email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'വിജയകരമായി ലോഗൗട്ട് ചെയ്തു (Logged out successfully).');
    }

    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->role === 'announcer') {
            return redirect()->intended(route('announcer.index'));
        }

        if ($user->role === 'program_committee' || $user->role === 'program_coordinator') {
            return redirect()->intended(route('program-committee.dashboard'));
        }

        if ($user->role === 'media_team' || $user->role === 'media_manager') {
            return redirect()->intended(route('media.dashboard'));
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isJudge()) {
            return redirect()->intended(route('judge.dashboard'));
        }

        if ($user->role === 'green_room_coordinator') {
            return redirect()->intended(route('greenroom.index'));
        }

        if ($user->isLeader()) {
            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/leader')) {
                return redirect()->intended(route('leader.dashboard'));
            }
            session()->forget('url.intended');

            return redirect()->route('leader.dashboard');
        }

        if ($user->isStudent()) {
            return redirect()->intended(route('student.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
