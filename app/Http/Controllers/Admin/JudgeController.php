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
            'password' => ['nullable', 'string', 'min:6'],
            'designation' => ['nullable', 'string'],
            'specialization' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['exists:programs,id'],
        ]);

        $email = $validated['email'] ?? (Str::slug($validated['name']).rand(100, 999).'@judge.festfloww');
        $password = $validated['password'] ?? 'judge'.rand(1000, 9999);
        $accessCode = ! empty($validated['access_code']) ? $validated['access_code'] : self::generateToughPin(4);

        // Create User account for judge
        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($password),
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

        return redirect()->route('admin.judges.index')->with('success', "Judge '{$judge->name}' registered with PIN: {$accessCode}.");
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
            'designation' => ['nullable', 'string'],
            'specialization' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['exists:programs,id'],
        ]);

        $old = $judge->toArray();
        $judge->update($validated);

        if (isset($validated['program_ids'])) {
            $judge->programs()->sync($validated['program_ids']);
        }

        if ($judge->user) {
            $judge->user->update([
                'name' => $validated['name'],
                'phone' => $validated['contact'] ?? null,
            ]);
        }

        AuditLogger::log('update_judge', $judge, $old, $judge->toArray());

        return redirect()->route('admin.judges.index')->with('success', "Judge '{$judge->name}' details updated.");
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
