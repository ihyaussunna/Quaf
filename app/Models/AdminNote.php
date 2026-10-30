<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AdminNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'color',
        'is_pinned',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::ensureSchema();
    }

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (! Schema::hasTable('admin_notes')) {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable) {
                }
            }

            if (! Schema::hasTable('admin_notes')) {
                Schema::create('admin_notes', function (Blueprint $table) {
                    $table->id();
                    $table->string('title', 255)->nullable();
                    $table->longText('content');
                    $table->string('category', 50)->default('general');
                    $table->string('color', 30)->default('slate');
                    $table->boolean('is_pinned')->default(false);
                    $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();

                    $table->index(['is_pinned', 'created_at']);
                    $table->index('category');
                });
            }

            $checked = true;
        } catch (\Throwable) {
            // Fail safely
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
