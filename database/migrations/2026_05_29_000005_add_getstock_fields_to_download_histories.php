<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('download_histories', function (Blueprint $table) {
            $table->string('getstock_slug')->nullable()->after('provider');
            $table->string('getstock_item_id')->nullable()->after('getstock_slug');
            $table->string('getstock_type')->nullable()->after('getstock_item_id');
            $table->boolean('is_premium')->default(false)->after('xu_cost');
        });
    }

    public function down(): void
    {
        Schema::table('download_histories', function (Blueprint $table) {
            $table->dropColumn(['getstock_slug', 'getstock_item_id', 'getstock_type', 'is_premium']);
        });
    }
};