<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'max_participants')) {
                $table->unsignedInteger('max_participants')->nullable()->after('participant_count');
            }
            if (! Schema::hasColumn('programs', 'max_participants_per_group')) {
                $table->unsignedInteger('max_participants_per_group')->default(2)->after('max_participants');
            }
            if (! Schema::hasColumn('programs', 'individual_limit_counted')) {
                $table->boolean('individual_limit_counted')->default(true)->after('max_participants_per_group');
            }
            if (! Schema::hasColumn('programs', 'mix_zone_open_to_all')) {
                $table->boolean('mix_zone_open_to_all')->default(true)->after('individual_limit_counted');
            }
            if (! Schema::hasColumn('programs', 'eligibility_rules')) {
                $table->json('eligibility_rules')->nullable()->after('mix_zone_open_to_all');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'max_participants',
                'max_participants_per_group',
                'individual_limit_counted',
                'mix_zone_open_to_all',
                'eligibility_rules',
            ]);
        });
    }
};
