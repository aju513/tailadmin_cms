<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table): void {
            $table->foreignId('notice_page_id')->nullable()->index()->constrained('pages')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notices', fn (Blueprint $table) => $table->dropConstrainedForeignId('notice_page_id'));
    }
};
