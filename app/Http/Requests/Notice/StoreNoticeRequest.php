<?php

namespace App\Http\Requests\Notice;

class StoreNoticeRequest extends SaveNoticeRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.create') ?? false;
    }
}
