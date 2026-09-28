<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->json('title_translations')->nullable();
            $table->json('summary_translations')->nullable();
            $table->json('body_translations')->nullable();
        });

        DB::table('pages')->orderBy('id')->chunkById(100, function ($pages): void {
            foreach ($pages as $page) {
                DB::table('pages')->where('id', $page->id)->update([
                    'title_translations' => json_encode(['en' => $page->title], JSON_THROW_ON_ERROR),
                    'summary_translations' => $page->summary === null ? null : json_encode(['en' => $page->summary], JSON_THROW_ON_ERROR),
                    'body_translations' => $page->body === null ? null : json_encode(['en' => $page->body], JSON_THROW_ON_ERROR),
                ]);
            }
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['title', 'summary', 'body']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->renameColumn('title_translations', 'title');
            $table->renameColumn('summary_translations', 'summary');
            $table->renameColumn('body_translations', 'body');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('title_original')->nullable();
            $table->text('summary_original')->nullable();
            $table->longText('body_original')->nullable();
        });

        DB::table('pages')->orderBy('id')->chunkById(100, function ($pages): void {
            foreach ($pages as $page) {
                DB::table('pages')->where('id', $page->id)->update([
                    'title_original' => json_decode($page->title ?: '{}', true, 512, JSON_THROW_ON_ERROR)['en'] ?? '',
                    'summary_original' => json_decode($page->summary ?: '{}', true, 512, JSON_THROW_ON_ERROR)['en'] ?? null,
                    'body_original' => json_decode($page->body ?: '{}', true, 512, JSON_THROW_ON_ERROR)['en'] ?? null,
                ]);
            }
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn(['title', 'summary', 'body']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->renameColumn('title_original', 'title');
            $table->renameColumn('summary_original', 'summary');
            $table->renameColumn('body_original', 'body');
        });
    }
};
