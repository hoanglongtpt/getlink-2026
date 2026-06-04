<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_providers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('display_name')->nullable();
            $table->unsignedBigInteger('xu_cost')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_providers');
    }
};
