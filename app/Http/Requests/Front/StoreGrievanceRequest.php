<?php

namespace App\Http\Requests\Front;

use App\Repositories\Contracts\GrievanceRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(GrievanceRepositoryInterface::class)->publishedPage((int) $this->route('pageId'));

        return true;
    }

    public function rules(): array
    {
        $profile = config('settings.uploads.grievance_attachment');

        return [
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:'.$profile['max_size_kb'], 'mimes:'.implode(',', $profile['mimes']), 'extensions:'.implode(',', $profile['mimes'])],
            'recaptcha_token' => ['required', 'string', 'max:4096'],
            'lang' => ['nullable', Rule::in(['en', 'ne'])],
        ];
    }
}
