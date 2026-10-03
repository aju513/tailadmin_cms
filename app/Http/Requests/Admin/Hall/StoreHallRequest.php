<?php

namespace App\Http\Requests\Admin\Hall;

class StoreHallRequest extends SaveHallRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.create') ?? false;
    }
}
