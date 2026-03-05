<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookAvailabilityAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Cidadao') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
