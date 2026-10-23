<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FundReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::ensureSchema();
    }

    protected $fillable = [
        'receipt_number',
        'donor_name',
        'donor_phone',
        'donor_place',
        'amount',
        'amount_in_words',
        'payment_method',
        'transaction_reference',
        'category',
        'notes',
        'screenshot_path',
        'whatsapp_number',
        'collector_id',
        'collector_name',
        'student_id',
        'group_id',
        'is_cancelled',
        'cancelled_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_cancelled' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Retrieve the model for a bound value, including trashed receipts so vouchers remain viewable.
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        return $query->withTrashed()->where(function ($q) use ($field, $value) {
            $q->where($field, $value);
            if ($field === 'id' && ! is_numeric($value)) {
                $q->orWhere('receipt_number', $value);
            }
        });
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        if (! $this->screenshot_path) {
            return null;
        }

        return asset('storage/'.$this->screenshot_path);
    }

    /**
     * Generate unique sequential receipt number (e.g. Q9-RCP-2026-0001).
     */
    public static function nextReceiptNumber(): string
    {
        static::ensureSchema();

        $prefix = 'QFR-26-';

        $lastReceipt = static::withTrashed()
            ->where(function ($q) use ($prefix) {
                $q->where('receipt_number', 'like', "{$prefix}%")
                    ->orWhere('receipt_number', 'like', 'Q9-RCP-%');
            })
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastReceipt && preg_match('/-(\d+)$/', $lastReceipt->receipt_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $nextSeq, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Convert numeric amount to words (Indian denomination).
     */
    public static function amountInWords(float|int|string $number): string
    {
        $number = (float) $number;
        $no = (int) floor($number);
        $decimal = (int) round(($number - $no) * 100);

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety',
        ];

        if ($no === 0) {
            $rupeesText = 'Zero';
        } else {
            $parts = [];

            // Crores
            if ($no >= 10000000) {
                $crores = (int) floor($no / 10000000);
                $parts[] = static::convertSubHundred($crores, $words).' Crore';
                $no %= 10000000;
            }

            // Lakhs
            if ($no >= 100000) {
                $lakhs = (int) floor($no / 100000);
                $parts[] = static::convertSubHundred($lakhs, $words).' Lakh';
                $no %= 100000;
            }

            // Thousands
            if ($no >= 1000) {
                $thousands = (int) floor($no / 1000);
                $parts[] = static::convertSubHundred($thousands, $words).' Thousand';
                $no %= 1000;
            }

            // Hundreds
            if ($no >= 100) {
                $hundreds = (int) floor($no / 100);
                $parts[] = ($words[$hundreds] ?? '').' Hundred';
                $no %= 100;
            }

            // Remainder
            if ($no > 0) {
                $parts[] = static::convertSubHundred($no, $words);
            }

            $rupeesText = implode(' ', array_filter($parts));
        }

        $paiseText = '';
        if ($decimal > 0) {
            $paiseText = ' and '.static::convertSubHundred($decimal, $words).' Paise';
        }

        return trim($rupeesText).' Rupees'.$paiseText.' Only';
    }

    /**
     * Helper to convert numbers up to 99 to words.
     */
    private static function convertSubHundred(int $val, array $words): string
    {
        if ($val <= 20) {
            return $words[$val] ?? '';
        }
        $tens = (int) (floor($val / 10) * 10);
        $units = $val % 10;

        return trim(($words[$tens] ?? '').' '.($words[$units] ?? ''));
    }

    /**
     * Self-heal and dynamically ensure fund receipt schema and collector credentials exist.
     */
    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            if (! Schema::hasTable('fund_receipt_settings') || ! Schema::hasTable('fund_receipts')) {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable) {
                }
            }

            if (! Schema::hasTable('fund_receipt_settings')) {
                Schema::create('fund_receipt_settings', function (Blueprint $table) {
                    $table->id();
                    $table->string('header_title')->default('QUAF SEASON 09');
                    $table->string('organization_name')->default('JAMIA IHYA-US-SUNNA');
                    $table->string('subtitle')->default('Grand Festival Fund Collection Voucher');
                    $table->string('text_alignment', 20)->default('center');
                    $table->string('font_size', 20)->default('normal');
                    $table->string('padding_size', 20)->default('normal');
                    $table->string('primary_color', 20)->default('#be1e2d');
                    $table->string('accent_color', 20)->default('#d97706');
                    $table->string('text_color', 20)->default('#0f172a');
                    $table->string('bg_color', 20)->default('#ffffff');
                    $table->boolean('show_logo')->default(true);
                    $table->string('logo_path')->nullable();
                    $table->text('footer_text')->nullable();
                    $table->integer('rcp_x')->default(890);
                    $table->integer('rcp_y')->default(175);
                    $table->integer('rcp_font_size')->default(20);
                    $table->string('rcp_color', 25)->default('#5c5a5b');
                    $table->string('rcp_align', 10)->default('left');

                    $table->integer('date_x')->default(830);
                    $table->integer('date_y')->default(230);
                    $table->integer('date_font_size')->default(20);
                    $table->string('date_color', 25)->default('#5c5a5b');
                    $table->string('date_align', 10)->default('left');

                    $table->integer('donor_x')->default(563);
                    $table->integer('donor_y')->default(610);
                    $table->integer('donor_font_size')->default(40);
                    $table->string('donor_color', 25)->default('#ffffff');
                    $table->string('donor_align', 10)->default('center');

                    $table->integer('amount_x')->default(563);
                    $table->integer('amount_y')->default(755);
                    $table->integer('amount_font_size')->default(40);
                    $table->string('amount_color', 25)->default('#000000');
                    $table->string('amount_align', 10)->default('center');

                    $table->timestamps();
                });

                DB::table('fund_receipt_settings')->insert([
                    'header_title' => 'QUAF SEASON 09',
                    'organization_name' => 'JAMIA IHYA-US-SUNNA',
                    'subtitle' => 'Grand Festival Fund Collection Voucher',
                    'text_alignment' => 'center',
                    'font_size' => 'normal',
                    'padding_size' => 'normal',
                    'primary_color' => '#be1e2d',
                    'accent_color' => '#d97706',
                    'text_color' => '#0f172a',
                    'bg_color' => '#ffffff',
                    'show_logo' => true,
                    'footer_text' => 'Thank you for your generous contribution towards QUAF 9.0!',
                    'authorized_by' => 'QUAF Finance Samithi',
                    'rcp_x' => 890,
                    'rcp_y' => 175,
                    'rcp_font_size' => 20,
                    'rcp_color' => '#5c5a5b',
                    'rcp_align' => 'left',
                    'date_x' => 830,
                    'date_y' => 230,
                    'date_font_size' => 20,
                    'date_color' => '#5c5a5b',
                    'date_align' => 'left',
                    'donor_x' => 563,
                    'donor_y' => 610,
                    'donor_font_size' => 40,
                    'donor_color' => '#ffffff',
                    'donor_align' => 'center',
                    'amount_x' => 563,
                    'amount_y' => 755,
                    'amount_font_size' => 40,
                    'amount_color' => '#000000',
                    'amount_align' => 'center',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $columns = [
                    'rcp_x' => fn ($t) => $t->integer('rcp_x')->default(890),
                    'rcp_y' => fn ($t) => $t->integer('rcp_y')->default(175),
                    'rcp_font_size' => fn ($t) => $t->integer('rcp_font_size')->default(20),
                    'rcp_color' => fn ($t) => $t->string('rcp_color', 25)->default('#5c5a5b'),
                    'rcp_align' => fn ($t) => $t->string('rcp_align', 10)->default('left'),

                    'date_x' => fn ($t) => $t->integer('date_x')->default(830),
                    'date_y' => fn ($t) => $t->integer('date_y')->default(230),
                    'date_font_size' => fn ($t) => $t->integer('date_font_size')->default(20),
                    'date_color' => fn ($t) => $t->string('date_color', 25)->default('#5c5a5b'),
                    'date_align' => fn ($t) => $t->string('date_align', 10)->default('left'),

                    'donor_x' => fn ($t) => $t->integer('donor_x')->default(563),
                    'donor_y' => fn ($t) => $t->integer('donor_y')->default(610),
                    'donor_font_size' => fn ($t) => $t->integer('donor_font_size')->default(40),
                    'donor_color' => fn ($t) => $t->string('donor_color', 25)->default('#ffffff'),
                    'donor_align' => fn ($t) => $t->string('donor_align', 10)->default('center'),

                    'amount_x' => fn ($t) => $t->integer('amount_x')->default(563),
                    'amount_y' => fn ($t) => $t->integer('amount_y')->default(755),
                    'amount_font_size' => fn ($t) => $t->integer('amount_font_size')->default(40),
                    'amount_color' => fn ($t) => $t->string('amount_color', 25)->default('#000000'),
                    'amount_align' => fn ($t) => $t->string('amount_align', 10)->default('center'),
                ];

                Schema::table('fund_receipt_settings', function (Blueprint $table) use ($columns) {
                    foreach ($columns as $columnName => $callback) {
                        if (! Schema::hasColumn('fund_receipt_settings', $columnName)) {
                            $callback($table);
                        }
                    }
                });
            }

            if (! Schema::hasTable('fund_receipts')) {
                Schema::create('fund_receipts', function (Blueprint $table) {
                    $table->id();
                    $table->string('receipt_number', 50)->unique();
                    $table->string('donor_name');
                    $table->string('donor_phone', 30)->nullable();
                    $table->string('whatsapp_number', 30)->nullable();
                    $table->string('donor_place', 100)->nullable();
                    $table->decimal('amount', 12, 2);
                    $table->string('amount_in_words')->nullable();
                    $table->string('payment_method', 30)->default('cash');
                    $table->string('transaction_reference', 100)->nullable();
                    $table->string('screenshot_path')->nullable();
                    $table->string('category', 100)->default('Festival Fund');
                    $table->text('notes')->nullable();
                    $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('collector_name')->nullable();
                    $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                    $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                    $table->boolean('is_cancelled')->default(false);
                    $table->string('cancelled_reason')->nullable();
                    $table->timestamp('cancelled_at')->nullable();
                    $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->softDeletes();
                    $table->timestamps();

                    $table->index(['created_at', 'is_cancelled']);
                    $table->index(['donor_phone', 'donor_name']);
                    $table->index('collector_id');
                    $table->index('student_id');
                    $table->index('group_id');
                });
            } else {
                if (! Schema::hasColumn('fund_receipts', 'student_id')) {
                    Schema::table('fund_receipts', function (Blueprint $table) {
                        $table->foreignId('student_id')->nullable()->after('collector_name')->constrained('students')->nullOnDelete();
                    });
                }
                if (! Schema::hasColumn('fund_receipts', 'group_id')) {
                    Schema::table('fund_receipts', function (Blueprint $table) {
                        $table->foreignId('group_id')->nullable()->after('student_id')->constrained('groups')->nullOnDelete();
                    });
                }
                if (! Schema::hasColumn('fund_receipts', 'screenshot_path')) {
                    Schema::table('fund_receipts', function (Blueprint $table) {
                        $table->string('screenshot_path')->nullable()->after('transaction_reference');
                    });
                }
                if (! Schema::hasColumn('fund_receipts', 'whatsapp_number')) {
                    Schema::table('fund_receipts', function (Blueprint $table) {
                        $table->string('whatsapp_number', 30)->nullable()->after('donor_phone');
                    });
                }
            }

            if (Schema::hasTable('users')) {
                User::firstOrCreate(
                    ['email' => 'fund@quaf.fest'],
                    [
                        'name' => 'QUAF Fund Collection Desk',
                        'password' => Hash::make('Fund#2026@Quaf!'),
                        'plain_password' => 'Fund#2026@Quaf!',
                        'role' => 'fund_collector',
                        'is_active' => true,
                    ]
                );
            }

            $checked = true;
        } catch (\Throwable) {
        }
    }
}
