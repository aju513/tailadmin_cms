<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('content_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('content_authors')->nullOnDelete();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('banner_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('social_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('news_content_tag', function (Blueprint $table): void {
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('content_tag_id')->constrained('content_tags')->cascadeOnDelete();
            $table->primary(['news_id', 'content_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_content_tag');
        Schema::dropIfExists('news');
    }
};
