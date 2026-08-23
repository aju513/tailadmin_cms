<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->foreignId('banner_media_id')->nullable()->after('meta_description')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('social_media_id')->nullable()->after('banner_media_id')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropForeign(['banner_media_id']);
            $table->dropForeign(['social_media_id']);
            $table->dropColumn(['banner_media_id', 'social_media_id']);
        });
    }
};
