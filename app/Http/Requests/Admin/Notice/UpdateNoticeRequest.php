<?php

namespace App\Http\Requests\Admin\Notice;

class UpdateNoticeRequest extends SaveNoticeRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.edit') ?? false;
    }
}
