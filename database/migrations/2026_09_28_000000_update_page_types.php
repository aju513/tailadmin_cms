<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'standard' => 'article',
            'contact' => 'contact_us',
            'photo' => 'gallery',
            'video' => 'videos',
            'faq' => 'faqs',
            'legal' => 'resource',
        ] as $old => $new) {
            DB::table('pages')->where('page_type', $old)->update(['page_type' => $new]);
        }

        Schema::table('pages', function (Blueprint $table): void {
            $table->string('page_type')->default('article')->change();
        });
    }

    public function down(): void
    {
        foreach ([
            'contact_us' => 'contact',
            'gallery' => 'photo',
            'videos' => 'video',
            'faqs' => 'faq',
            'resource' => 'legal',
            'news' => 'article',
            'notices' => 'article',
            'hall' => 'standard',
        ] as $new => $old) {
            DB::table('pages')->where('page_type', $new)->update(['page_type' => $old]);
        }

        Schema::table('pages', function (Blueprint $table): void {
            $table->string('page_type')->default('standard')->change();
        });
    }
};
