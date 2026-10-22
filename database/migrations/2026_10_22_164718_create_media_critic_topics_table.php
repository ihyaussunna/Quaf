<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_critic_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('topic_number')->unique();
            $table->string('code_letter', 5)->unique();
            $table->text('title');
            $table->foreignId('group_id')->nullable()->unique()->constrained('groups')->nullOnDelete();
            $table->foreignId('picked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('picked_at')->nullable();
            $table->string('selection_method', 50)->default('card');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        // Seed initial 5 topics for Media Critic competition
        $now = now();
        DB::table('media_critic_topics')->insertOrIgnore([
            [
                'topic_number' => 1,
                'code_letter' => 'A',
                'title' => 'വഖ്ഫ് ബോർഡ് പുനഃസംഘടനയും പ്രാതിനിധ്യ പ്രശ്നങ്ങളും.',
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'topic_number' => 2,
                'code_letter' => 'B',
                'title' => 'സി ജെ പി സമരം: ജനകീയ അവശ്യങ്ങളോടുള്ള കേന്ദ്ര സമീപനം.',
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'topic_number' => 3,
                'code_letter' => 'C',
                'title' => 'സമസ്ത സർക്കുലറും സ്ത്രീ വിവാദവും',
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'topic_number' => 4,
                'code_letter' => 'D',
                'title' => 'ഫലസ്തീൻ ജനതയോടുള്ള ഇസ്രായേൽ വംശഹത്യ',
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'topic_number' => 5,
                'code_letter' => 'E',
                'title' => 'സഊദിയിൽ ഹൂത്തി ആക്രമണം',
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_critic_topics');
    }
};
