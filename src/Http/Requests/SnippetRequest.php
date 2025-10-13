<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
