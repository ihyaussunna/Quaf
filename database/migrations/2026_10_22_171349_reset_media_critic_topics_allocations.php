<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('media_critic_topics')) {
            DB::table('media_critic_topics')->update([
                'group_id' => null,
                'picked_by_user_id' => null,
                'picked_at' => null,
                'selection_method' => 'card',
                'is_locked' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
