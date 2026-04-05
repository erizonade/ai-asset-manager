<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('schedule_type'); // daily, weekly, custom
            $table->time('run_at')->nullable();
            $table->json('days')->nullable(); // [1,3,5] for weekly (1=Monday)
            $table->boolean('generate_prompts')->default(true);
            $table->boolean('generate_assets')->default(false);
            $table->integer('asset_count')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};