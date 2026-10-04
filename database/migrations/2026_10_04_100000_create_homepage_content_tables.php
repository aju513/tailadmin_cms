<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('social_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            foreach (['created_by', 'updated_by'] as $column) {
                $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
            }
            $table->timestamps();
        });

        Schema::create('homepage_gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('homepage_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $settings = DB::table('site_settings')->whereIn('key', ['site_name', 'about_title', 'about_description', 'meta_description'])->pluck('value', 'key');
        $albums = DB::table('gallery_albums')->where('status', 'published')
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereExists(fn ($photos) => $photos->selectRaw('1')->from('gallery_photos')
                ->join('media_assets', 'media_assets.id', '=', 'gallery_photos.media_asset_id')
                ->whereColumn('gallery_photos.gallery_album_id', 'gallery_albums.id'))
            ->orderBy('sort_order')->orderBy('id')->limit(8)->get();
        if ($settings->isEmpty() && $albums->isEmpty()) {
            return;
        }

        $id = DB::table('homepage_contents')->insertGetId([
            'key' => 'home',
            'title' => json_encode(['en' => $settings->get('about_title', config('frontend.about_title')) ?: $settings->get('site_name') ?: config('frontend.name')], JSON_THROW_ON_ERROR),
            'subtitle' => json_encode(['en' => 'Our About Us'], JSON_THROW_ON_ERROR),
            'body' => json_encode(['en' => $settings->get('about_description', config('frontend.about_description'))], JSON_THROW_ON_ERROR),
            'meta_description' => $settings['meta_description'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Keep the images currently displayed by the public About section.
        foreach ($albums as $index => $album) {
            $mediaId = DB::table('gallery_photos')->where('gallery_album_id', $album->id)->orderBy('sort_order')->orderBy('id')->value('media_asset_id');
            if ($mediaId) {
                DB::table('homepage_gallery_images')->insert([
                    'homepage_content_id' => $id, 'media_asset_id' => $mediaId, 'sort_order' => $index,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_gallery_images');
        Schema::dropIfExists('homepage_contents');
    }
};
