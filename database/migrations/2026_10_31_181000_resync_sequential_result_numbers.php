<?php

use App\Models\Result;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Result::ensureSchema();
        Result::resyncResultNumbers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for sequence numbers
    }
};
