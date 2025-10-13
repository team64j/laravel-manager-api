<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChunkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->can('edit_chunk'),
            'store' => auth()->user()->can('new_chunk'),
            'update' => auth()->user()->can('save_chunk'),
            'destroy' => auth()->user()->can('delete_chunk'),
            default => auth()->user()->canAny(['edit_chunk', 'new_chunk', 'save_chunk', 'delete_chunk']),
        };
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store', 'update' => [
                'name'        => 'string|required',
                'snippet'     => 'string|nullable',
                'description' => 'string|nullable',
                'locked'      => 'int',
                'category'    => 'int',
                'disabled'    => 'int',
            ],
            default => []
        };
    }
}
