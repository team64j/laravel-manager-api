<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Team64j\LaravelManagerApi\Models\Category;

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
                'attributes.name'        => 'string|required',
                'attributes.snippet'     => 'string|nullable',
                'attributes.description' => 'string|nullable',
                'attributes.locked'      => 'int',
                'attributes.category'    => 'required|int',
                'attributes.disabled'    => 'int',
            ],
            default => []
        };
    }

    public function validationData(): array
    {
        if (is_string($this->input('attributes.category'))) {
            $this->merge(
                [
                    'attributes.category' => Category::query()->firstOrCreate([
                        'category' => $this->input('attributes.category'),
                    ])->getKey(),
                ]
            );
        }

        return parent::validationData();
    }
}
