<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Team64j\LaravelManagerApi\Models\Category;

class SnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->can('edit_snippet'),
            'store' => auth()->user()->can('new_snippet'),
            'update' => auth()->user()->can('save_snippet'),
            'destroy' => auth()->user()->can('delete_snippet'),
            default => auth()->user()->canAny(['edit_snippet', 'new_snippet', 'save_snippet', 'delete_snippet']),
        };
    }

    public function rules(): array
    {
        return [];
    }

    public function validationData(): array
    {
        if (is_string($this->input('category'))) {
            $this->merge(
                [
                    'category' => Category::query()->firstOrCreate([
                        'category' => $this->input('category'),
                    ])->getKey(),
                ]
            );
        }

        return parent::validationData();
    }
}
