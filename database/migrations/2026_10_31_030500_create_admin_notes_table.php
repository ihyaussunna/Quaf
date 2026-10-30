<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notes');
    }
};
