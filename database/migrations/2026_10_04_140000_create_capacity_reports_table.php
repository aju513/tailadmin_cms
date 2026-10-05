<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacity_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('fiscal_year', 20)->unique();
            $table->json('development');
            $table->json('collaboration');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Keep the original setting intact while importing existing reporting years.
        $reports = json_decode(DB::table('site_settings')->where('key', 'capacity_reports')->value('value') ?? '[]', true);
        $labels = ['training_programs' => 'Total training programs', 'participants' => 'Total participants', 'in_service_programs' => 'In-service training programs', 'in_service_participants' => 'In-service participants', 'materials' => 'Training materials developed', 'dialogues' => 'Issue-focused dialogues', 'research' => 'Research studies', 'consultancy' => 'Consultancy services'];
        foreach (is_array($reports) ? $reports : [] as $report) {
            if (! is_array($report) || ! is_string($report['year'] ?? null) || trim($report['year']) === '' || mb_strlen(trim($report['year'])) > 20) {
                continue;
            }
            $data = ['fiscal_year' => trim($report['year']), 'created_at' => now(), 'updated_at' => now()];
            foreach (['development', 'collaboration'] as $group) {
                $rows = [];
                foreach ($labels as $key => $label) {
                    $value = $report[$group][$key] ?? null;
                    $rows[] = ['key' => $label, 'value' => filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000000]]) !== false ? (int) $value : null];
                }
                $data[$group] = json_encode($rows, JSON_THROW_ON_ERROR);
            }
            DB::table('capacity_reports')->insertOrIgnore($data);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_reports');
    }
};
