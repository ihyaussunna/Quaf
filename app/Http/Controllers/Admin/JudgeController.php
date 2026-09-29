<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Judge;
use App\Models\Program;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JudgeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Judge::with(['user', 'programs.category'])
            ->withCount('programs');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $judges = $query->orderBy('name')->get();
        $programs = Program::with('category')->orderBy('name')->get();

        return view('admin.judges.index', compact('judges', 'programs', 'search'));
    }

    public function create(): View
    {
        $programs = Program::orderBy('name')->get();

        return view('admin.judges.create', compact('programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'access_code' => ['nullable', 'string', 'max:10'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:4'],
            'designation' => ['nullable', 'string'],
            'specialization' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['exists:programs,id'],
        ]);

        $email = ! empty($validated['email'])
            ? trim($validated['email'])
            : (Str::slug($validated['name']).rand(100, 999).'@quaf.fest');

        $rawPassword = ! empty($validated['password'])
            ? trim($validated['password'])
            : ('Judge@'.rand(1000, 9999));

        $accessCode = ! empty($validated['access_code']) ? trim($validated['access_code']) : self::generateToughPin(4);

        // Create User account for judge
        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($rawPassword),
            'plain_password' => $rawPassword,
            'role' => 'judge',
            'phone' => $validated['contact'] ?? null,
            'is_active' => true,
        ]);

        $judge = Judge::create([
            'user_id' => $user->id,
            'access_code' => $accessCode,
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? 'Official Judge',
            'specialization' => $validated['specialization'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        if (! empty($validated['program_ids'])) {
            $judge->programs()->sync($validated['program_ids']);
        }

        AuditLogger::log('create_judge', $judge, null, $judge->toArray());

        return redirect()->route('admin.judges.index')->with('success', "Judge '{$judge->name}' registered successfully! Password: {$rawPassword} | PIN: {$accessCode}");
    }

    public function edit(Judge $judge): View
    {
        $programs = Program::orderBy('name')->get();
        $judge->load(['programs', 'user']);

        return view('admin.judges.edit', compact('judge', 'programs'));
    }

    public function update(Request $request, Judge $judge): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'access_code' => ['nullable', 'string', 'max:10'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($judge->user_id)],
            'password' => ['nullable', 'string', 'min:4'],
            'designation' => ['nullable', 'string'],
            'specialization' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['exists:programs,id'],
        ]);

        $old = $judge->toArray();
        $judge->update([
            'name' => $validated['name'],
            'access_code' => $validated['access_code'] ?? $judge->access_code,
            'designation' => $validated['designation'] ?? $judge->designation,
            'specialization' => $validated['specialization'] ?? $judge->specialization,
            'contact' => $validated['contact'] ?? $judge->contact,
            'bio' => $validated['bio'] ?? $judge->bio,
            'notes' => $validated['notes'] ?? $judge->notes,
        ]);

        if (isset($validated['program_ids'])) {
            $judge->programs()->sync($validated['program_ids']);
        }

        if ($judge->user) {
            $userUpdates = [
                'name' => $validated['name'],
                'phone' => $validated['contact'] ?? null,
            ];
            if (! empty($validated['email'])) {
                $userUpdates['email'] = trim($validated['email']);
            }
            if (! empty($validated['password'])) {
                $userUpdates['password'] = Hash::make(trim($validated['password']));
                $userUpdates['plain_password'] = trim($validated['password']);
            }
            $judge->user->update($userUpdates);
        } else {
            $email = ! empty($validated['email'])
                ? trim($validated['email'])
                : (Str::slug($validated['name']).rand(100, 999).'@quaf.fest');
            $password = ! empty($validated['password'])
                ? trim($validated['password'])
                : ('Judge@'.rand(1000, 9999));

            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make($password),
                'plain_password' => $password,
                'role' => 'judge',
                'phone' => $validated['contact'] ?? null,
                'is_active' => true,
            ]);
            $judge->update(['user_id' => $user->id]);
        }

        AuditLogger::log('update_judge', $judge, $old, $judge->toArray());

        $passwordNotice = ! empty($validated['password']) ? " Password updated to: {$validated['password']}." : '';

        return redirect()->route('admin.judges.index')->with('success', "Judge '{$judge->name}' details updated.{$passwordNotice}");
    }

    /**
     * Quick password update for judge from index table or modal.
     */
    public function updatePassword(Request $request, Judge $judge): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4'],
        ]);

        $newPassword = trim($validated['password']);

        if (! $judge->user) {
            $email = Str::slug($judge->name).rand(100, 999).'@quaf.fest';
            $user = User::create([
                'name' => $judge->name,
                'email' => $email,
                'password' => Hash::make($newPassword),
                'plain_password' => $newPassword,
                'role' => 'judge',
                'phone' => $judge->contact,
                'is_active' => true,
            ]);
            $judge->update(['user_id' => $user->id]);
        } else {
            $judge->user->update([
                'password' => Hash::make($newPassword),
                'plain_password' => $newPassword,
            ]);
        }

        AuditLogger::log('update_judge_password', $judge, null, ['updated_by' => auth()->id()]);

        return back()->with('success', "Password for Judge '{$judge->name}' updated to: {$newPassword}");
    }

    public function destroy(Judge $judge): RedirectResponse
    {
        $old = $judge->toArray();
        $name = $judge->name;
        $user = $judge->user;

        $judge->delete();
        $user?->delete();

        AuditLogger::log('delete_judge', null, $old, null);

        return redirect()->route('admin.judges.index')->with('success', "Judge '{$name}' deleted.");
    }

    /**
     * Generate a cryptographically secure, non-sequential tough PIN.
     */
    public static function generateToughPin(int $length = 4): string
    {
        $weakPatterns = [
            '0000', '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999',
            '1234', '2345', '3456', '4567', '5678', '6789', '4321', '5432', '6543', '7654',
            '1001', '1002', '1003', '1004', '1005', '2001', '2002', '1212', '1313', '1414',
            '0123', '9876', '8765', '7654', '6543', '5432', '4321', '3210',
        ];

        do {
            $min = (int) pow(10, $length - 1);
            $max = (int) pow(10, $length) - 1;
            $pin = (string) random_int($min, $max);

            $isWeak = in_array($pin, $weakPatterns);

            if (count(array_unique(str_split($pin))) <= 1) {
                $isWeak = true;
            }

            $isSequential = true;
            for ($i = 0; $i < strlen($pin) - 1; $i++) {
                if (abs((int) $pin[$i] - (int) $pin[$i + 1]) !== 1) {
                    $isSequential = false;
                    break;
                }
            }
            if ($isSequential) {
                $isWeak = true;
            }

            $exists = Judge::where('access_code', $pin)->exists();
        } while ($isWeak || $exists);

        return $pin;
    }

    /**
     * Regenerate a tough PIN for the judge.
     */
    public function regeneratePin(Judge $judge): RedirectResponse
    {
        $oldPin = $judge->access_code;
        $newPin = self::generateToughPin(4);

        $judge->update(['access_code' => $newPin]);

        AuditLogger::log('regenerate_judge_pin', $judge, ['access_code' => $oldPin], ['access_code' => $newPin]);

        return back()->with('success', "Judge '{$judge->name}' നുള്ള പുതിയ ടഫ് പിൻ (PIN): {$newPin} വിജയകരമായി നൽകി.");
    }
}
