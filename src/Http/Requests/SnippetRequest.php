<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SnippetRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_snippet'),
            'store' => Gate::check('new_snippet'),
            'update' => Gate::check('save_snippet'),
            'destroy' => Gate::check('delete_snippet'),
            default => Gate::any(['edit_snippet', 'new_snippet', 'save_snippet', 'delete_snippet']),
        };
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
