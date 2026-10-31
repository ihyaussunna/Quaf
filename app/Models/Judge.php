<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Judge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_code',
        'name',
        'designation',
        'bio',
        'specialization',
        'notes',
        'contact',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'judge_assignments');
    }

    public function scoreSheets(): HasMany
    {
        return $this->hasMany(ScoreSheet::class);
    }

    /**
     * Self-heal credentials for legacy judges with missing plain_password.
     */
    public static function ensureCredentials(): void
    {
        self::with('user')->whereNotNull('access_code')->chunk(50, function ($judges) {
            foreach ($judges as $j) {
                if ($j->user && empty($j->user->plain_password)) {
                    $j->user->update([
                        'plain_password' => $j->access_code,
                    ]);
                }
            }
        });
    }

    /**
     * Ensure the 3 Universal Custom Judges exist with User accounts.
     *
     * @return array<int, Judge>
     */
    public static function ensureCustomJudges(): array
    {
        $judges = [];
        $definitions = [
            1 => ['name' => 'Judge 1', 'pin' => '1001', 'email' => 'judge1@quaf.fest'],
            2 => ['name' => 'Judge 2', 'pin' => '1002', 'email' => 'judge2@quaf.fest'],
            3 => ['name' => 'Judge 3', 'pin' => '1003', 'email' => 'judge3@quaf.fest'],
        ];

        foreach ($definitions as $num => $def) {
            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name' => $def['name'],
                    'password' => Hash::make('Judge@'.$def['pin']),
                    'plain_password' => $def['pin'],
                    'role' => 'judge',
                    'is_active' => true,
                ]
            );

            $judge = self::firstOrCreate(
                ['name' => $def['name']],
                [
                    'user_id' => $user->id,
                    'access_code' => $def['pin'],
                    'designation' => 'Official Stage Judge '.$num,
                    'specialization' => 'Stage Evaluation Desk',
                ]
            );

            if (! $judge->user_id || $judge->user_id !== $user->id) {
                $judge->update(['user_id' => $user->id]);
            }

            $judges[$num] = $judge;
        }

        return $judges;
    }
}
