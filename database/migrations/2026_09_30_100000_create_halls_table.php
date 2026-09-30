<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halls', function (Blueprint $table): void {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->string('building_name')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('capacity');
            $table->decimal('floor_area', 10, 2)->nullable();
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->json('booking_instructions')->nullable();
            $table->json('amenities')->nullable();
            $table->decimal('rental_rate', 12, 2)->nullable();
            $table->string('rate_unit', 20)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->string('availability_status', 20)->default('available')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            foreach (['thumbnail_media_id', 'banner_media_id', 'social_media_id'] as $column) {
                $table->foreignId($column)->nullable()->constrained('media_assets')->nullOnDelete();
            }
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            foreach (['created_by', 'updated_by', 'published_by'] as $column) {
                $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
            }
            $table->timestamps();
        });

        Schema::create('hall_gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['hall_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_gallery_images');
        Schema::dropIfExists('halls');
    }
};
