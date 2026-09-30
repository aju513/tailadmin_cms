<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pages', 'news', 'notices', 'resource_documents', 'halls', 'gallery_albums', 'videos'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->index(['status', 'published_at', 'id'], $tableName.'_public_listing_index');
            });
        }
    }

    public function down(): void
    {
        foreach (['pages', 'news', 'notices', 'resource_documents', 'halls', 'gallery_albums', 'videos'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex($tableName.'_public_listing_index');
            });
        }
    }
};
