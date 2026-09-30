<?php

namespace App\Http\Requests\Hall;

class UpdateHallRequest extends SaveHallRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.edit') ?? false;
    }
}
