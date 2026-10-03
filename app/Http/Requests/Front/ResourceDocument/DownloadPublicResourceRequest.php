<?php

namespace App\Http\Requests\Front\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class DownloadPublicResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
