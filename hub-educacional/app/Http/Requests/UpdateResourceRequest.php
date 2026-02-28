<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['sometimes', new Enum(ResourceType::class)],
            'url'         => ['sometimes', 'url', 'max:2048'],
            'tags'        => ['nullable', 'array', 'max:10'],
            'tags.*'      => ['string', 'max:50'],
        ];
    }
}
