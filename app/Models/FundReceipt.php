<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundReceipt extends Model
{
    use HasFactory, SoftDeletes;

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
        'collector_id',
        'collector_name',
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

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Generate unique sequential receipt number (e.g. Q9-RCP-2026-0001).
     */
    public static function nextReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = "Q9-RCP-{$year}-";

        $lastReceipt = static::withTrashed()
            ->where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastReceipt && preg_match('/-(\d+)$/', $lastReceipt->receipt_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
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
}
