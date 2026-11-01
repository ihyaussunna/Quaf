<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PresentationCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'cart_items',
        'total_coins',
        'total_items_count',
        'cash_required',
        'cash_received',
        'status',
        'notes',
        'operator_id',
        'operator_name',
        'cashier_id',
        'cashier_name',
        'cashier_received_at',
        'transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cart_items' => 'array',
            'total_coins' => 'integer',
            'total_items_count' => 'integer',
            'cash_required' => 'decimal:2',
            'cash_received' => 'decimal:2',
            'cashier_received_at' => 'datetime',
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
            if (! Schema::hasTable('presentation_carts')) {
                Schema::create('presentation_carts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                    $table->json('cart_items');
                    $table->integer('total_coins')->default(0);
                    $table->integer('total_items_count')->default(0);
                    $table->decimal('cash_required', 10, 2)->default(0.00);
                    $table->decimal('cash_received', 10, 2)->default(0.00);
                    $table->string('status', 30)->default('pending')->index();
                    $table->text('notes')->nullable();
                    $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('operator_name')->nullable();
                    $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('cashier_name')->nullable();
                    $table->timestamp('cashier_received_at')->nullable();
                    $table->foreignId('transaction_id')->nullable()->constrained('coin_transactions')->nullOnDelete();
                    $table->timestamps();

                    $table->index(['student_id', 'status']);
                });
            }
            $checked = true;
        } catch (\Throwable $e) {
            // Keep app running even if schema check fails
        }
    }

    /**
     * @return BelongsTo<Student, PresentationCart>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, PresentationCart>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * @return BelongsTo<User, PresentationCart>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * @return BelongsTo<CoinTransaction, PresentationCart>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CoinTransaction::class, 'transaction_id');
    }
}
