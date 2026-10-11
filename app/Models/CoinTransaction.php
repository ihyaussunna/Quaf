<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CoinTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'program_id',
        'entry_id',
        'result_id',
        'prize_position',
        'grade',
        'reward_rule_id',
        'coins',
        'transaction_type',
        'transaction_reference',
        'status',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'prize_position' => 'integer',
            'coins' => 'integer',
            'metadata' => 'array',
        ];
    }

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (! Schema::hasTable('coin_transactions')) {
                Schema::create('coin_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                    $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                    $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
                    $table->foreignId('entry_id')->nullable()->constrained('program_entries')->nullOnDelete();
                    $table->foreignId('result_id')->nullable()->constrained('results')->nullOnDelete();
                    $table->unsignedTinyInteger('prize_position');
                    $table->string('grade', 15)->nullable();
                    $table->string('reward_rule_id', 60)->default('QUAF09_GROUP_PROGRAM_REWARD_RULE_V1');
                    $table->integer('coins');
                    $table->string('transaction_type', 20)->default('credit');
                    $table->string('transaction_reference', 120)->unique();
                    $table->string('status', 20)->default('completed');
                    $table->string('description')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();

                    $table->index(['student_id', 'transaction_type']);
                    $table->index(['group_id', 'program_id']);
                    $table->unique(['result_id', 'entry_id', 'student_id', 'transaction_type'], 'coin_tx_res_entry_stu_type_unique');
                });
            }

            if (Schema::hasTable('students') && ! Schema::hasColumn('students', 'coins_cache')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->integer('coins_cache')->default(0)->after('points_cache');
                });
            }

            if (Schema::hasTable('results')) {
                Schema::table('results', function (Blueprint $table) {
                    if (! Schema::hasColumn('results', 'first_grade')) {
                        $table->string('first_grade', 10)->nullable()->after('first_entry_id');
                    }
                    if (! Schema::hasColumn('results', 'second_grade')) {
                        $table->string('second_grade', 10)->nullable()->after('second_entry_id');
                    }
                    if (! Schema::hasColumn('results', 'third_grade')) {
                        $table->string('third_grade', 10)->nullable()->after('third_entry_id');
                    }
                });
            }

            $checked = true;
        } catch (\Throwable) {
            // Silently ignore schema creation failures in constrained environments
        }
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ProgramEntry::class, 'entry_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }
}
