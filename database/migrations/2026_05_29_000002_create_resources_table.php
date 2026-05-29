<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('original_link')->unique();
            $table->string('provider')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->string('file_name')->nullable();
            $table->string('file_ext')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('google_drive_link')->nullable();
            $table->string('google_drive_file_id')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->string('status')->default('cached');
            $table->json('external_metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};