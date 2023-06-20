<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ChunkRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_chunk'),
            'store' => Gate::check('new_chunk'),
            'update' => Gate::check('save_chunk'),
            'destroy' => Gate::check('delete_chunk'),
            default => Gate::any(['edit_chunk', 'new_chunk', 'save_chunk', 'delete_chunk']),
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
