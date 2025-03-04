<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'select' => true,
            default => auth()->user()->can('category_manager'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store', 'update' => [
                'category' => 'required|string',
                'rank' => 'integer|nullable',
            ],
            default => []
        };
    }
}
