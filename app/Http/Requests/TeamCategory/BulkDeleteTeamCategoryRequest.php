<?php
namespace App\Http\Requests\TeamCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class BulkDeleteTeamCategoryRequest extends FormRequest { public function authorize(): bool { return $this->user()?->can('team-categories.delete') ?? false; } public function rules(): array { return ['categories'=>['required','array','min:1'],'categories.*'=>['integer','distinct',Rule::exists('team_categories','id')]]; } }
