<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_categories', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        Schema::create('notice_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            foreach (['created_by', 'updated_by'] as $column) {
                $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
            }
            $table->timestamps();
        });
        Schema::table('notices', fn (Blueprint $table) => $table->foreignId('notice_category_id')->nullable()->constrained()->restrictOnDelete());
        Schema::table('pages', fn (Blueprint $table) => $table->foreignId('notice_category_id')->nullable()->constrained()->restrictOnDelete());

        $types = [
            'general' => 'General Notices', 'tender' => 'Tender Notices',
            'press_release' => 'Press Releases', 'application' => 'Application Announcements',
        ];
        foreach ($types as $order => $name) {
            $id = DB::table('notice_categories')->insertGetId([
                'name' => $name, 'slug' => str_replace('_', '-', $order),
                'is_active' => true, 'sort_order' => array_search($order, array_keys($types), true),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('notices')->where('notice_type', $order)->update(['notice_category_id' => $id]);
            DB::table('pages')->where('page_type', 'notices')->where('notice_type', $order)->update(['notice_category_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->dropConstrainedForeignId('notice_category_id'));
        Schema::table('notices', fn (Blueprint $table) => $table->dropConstrainedForeignId('notice_category_id'));
        Schema::dropIfExists('notice_categories');
        Schema::table('resource_categories', fn (Blueprint $table) => $table->dropColumn('is_active'));
    }
};
