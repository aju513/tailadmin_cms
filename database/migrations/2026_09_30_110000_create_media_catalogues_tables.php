<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['gallery_albums', 'videos'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
                if ($name === 'gallery_albums') {
                    $table->date('event_date')->nullable();
                } else {
                    $table->string('video_url', 2048);
                    $table->string('category', 120)->nullable()->index();
                }
                $table->string('status')->default('draft')->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamp('published_at')->nullable()->index();
                foreach (['created_by', 'updated_by', 'published_by'] as $column) {
                    $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
                }
                $table->timestamps();
            });
        }
        Schema::create('gallery_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('caption', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('gallery_albums');
    }
};
