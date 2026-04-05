<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type'); // image, video
            $table->string('file_name')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('keywords')->nullable(); // Array of 49 keywords
            $table->string('thumbnail_path')->nullable();
            $table->enum('status', ['draft', 'ready', 'uploaded'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};