<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table): void {
            $table->string('notice_type')->default('general')->index();
            $table->timestamp('deadline_at')->nullable();
        });
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('notice_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn('notice_type'));
        Schema::table('notices', function (Blueprint $table): void {
            $table->dropIndex(['notice_type']);
            $table->dropColumn(['notice_type', 'deadline_at']);
        });
    }
};
