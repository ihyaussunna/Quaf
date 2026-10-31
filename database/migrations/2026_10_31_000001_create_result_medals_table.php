<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('result_medals')) {
            Schema::create('result_medals', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('first_image');
                $table->string('second_image');
                $table->string('third_image');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Insert default official medal set if not present
        $existing = DB::table('result_medals')->where('name', 'Classic Official Medals')->first();
        if (! $existing) {
            DB::table('result_medals')->insert([
                'name' => 'Classic Official Medals',
                'first_image' => '/images/medals/first.png',
                'second_image' => '/images/medals/second.png',
                'third_image' => '/images/medals/third.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('result_medals');
    }
};
