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
        // 1. Create result_templates table
        if (! Schema::hasTable('result_templates')) {
            Schema::create('result_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('image_path');
                $table->boolean('is_active')->default(true);
                $table->json('default_settings')->nullable();
                $table->timestamps();
            });
        }

        // 2. Create poster_settings table
        if (! Schema::hasTable('poster_settings')) {
            Schema::create('poster_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->nullable()->constrained('result_templates')->nullOnDelete();
                $table->string('layout_mode')->default('default');

                // Result Number
                $table->float('result_x')->default(733);
                $table->float('result_y')->default(238);
                $table->float('result_size')->default(78);
                $table->string('result_weight')->default('400');
                $table->string('result_color')->default('theme');

                // Category
                $table->float('category_x')->default(540);
                $table->float('category_y')->default(286);
                $table->float('category_size')->default(31);
                $table->string('category_weight')->default('300');
                $table->string('category_color')->default('white');
                $table->string('category_align')->default('center');

                // Competition / Program Name
                $table->float('competition_x')->default(540);
                $table->float('competition_y')->default(338);
                $table->float('competition_size')->default(46);
                $table->string('competition_weight')->default('600');
                $table->float('competition_max_width')->default(650);
                $table->float('competition_line_height')->default(42);
                $table->string('competition_color')->default('white');
                $table->string('competition_align')->default('center');

                // Winner Block Layout & Distances
                $table->float('block_left')->default(380);
                $table->float('first_top')->default(460);
                $table->float('row_gap')->default(98); // Line distance between winners
                $table->float('item_gap')->default(12); // Distance between medal and text
                $table->float('medal_size')->default(60);

                // Winner Text Styles
                $table->float('winner_name_size')->default(32);
                $table->string('winner_name_weight')->default('400');
                $table->string('winner_name_color')->default('white');
                $table->float('winner_unit_size')->default(21);
                $table->string('winner_unit_weight')->default('300');
                $table->string('winner_unit_color')->default('white');

                // Sponsor Area
                $table->string('sponsor_image')->nullable();
                $table->boolean('sponsor_enabled')->default(false);

                $table->timestamps();
            });
        }

        // 3. Add poster fields to results table
        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'poster_image')) {
                $table->string('poster_image')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('results', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('poster_image')->constrained('result_templates')->nullOnDelete();
            }
            if (! Schema::hasColumn('results', 'custom_poster_settings')) {
                $table->json('custom_poster_settings')->nullable()->after('template_id');
            }
            if (! Schema::hasColumn('results', 'is_media_published')) {
                $table->boolean('is_media_published')->default(false)->after('custom_poster_settings');
            }
            if (! Schema::hasColumn('results', 'media_published_at')) {
                $table->timestamp('media_published_at')->nullable()->after('is_media_published');
            }
        });

        // 4. Seed initial default template and settings
        $defaultTemplateImg = file_exists(public_path('images/result-templates/1778705272_srt1-01.png'))
            ? '/images/result-templates/1778705272_srt1-01.png'
            : (file_exists(public_path('images/result-templates/1778705439_srt2-01.png'))
                ? '/images/result-templates/1778705439_srt2-01.png'
                : '/images/dashboard-logo.png');

        $templateId = DB::table('result_templates')->insertGetId([
            'name' => 'QUAF 09 Official Royal Maroon Frame',
            'image_path' => $defaultTemplateImg,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (file_exists(public_path('images/result-templates/1778705439_srt2-01.png'))) {
            DB::table('result_templates')->insert([
                'name' => 'QUAF 09 Obsidian Dark Conclave Frame',
                'image_path' => '/images/result-templates/1778705439_srt2-01.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('poster_settings')->insert([
            'template_id' => $templateId,
            'layout_mode' => 'default',
            'result_x' => 733,
            'result_y' => 238,
            'result_size' => 78,
            'result_weight' => '700',
            'result_color' => 'theme',
            'category_x' => 540,
            'category_y' => 286,
            'category_size' => 31,
            'category_weight' => '400',
            'category_color' => 'white',
            'category_align' => 'center',
            'competition_x' => 540,
            'competition_y' => 338,
            'competition_size' => 46,
            'competition_weight' => '700',
            'competition_max_width' => 650,
            'competition_line_height' => 46,
            'competition_color' => 'white',
            'competition_align' => 'center',
            'block_left' => 380,
            'first_top' => 460,
            'row_gap' => 98,
            'item_gap' => 14,
            'medal_size' => 60,
            'winner_name_size' => 32,
            'winner_name_weight' => '700',
            'winner_name_color' => 'white',
            'winner_unit_size' => 22,
            'winner_unit_weight' => '400',
            'winner_unit_color' => 'white',
            'sponsor_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn([
                'poster_image',
                'template_id',
                'custom_poster_settings',
                'is_media_published',
                'media_published_at',
            ]);
        });

        Schema::dropIfExists('poster_settings');
        Schema::dropIfExists('result_templates');
    }
};
