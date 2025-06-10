<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TvRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'update', 'store' => [
                'attributes.name' => 'required|string',
                'attributes.description' => 'string',
                'attributes.caption' => 'string',
                'attributes.editor_type' => 'int',
                'attributes.category' => 'required|int',
                'attributes.type' => 'required|string',
                'attributes.elements' => 'string|nullable',
                'attributes.default_text' => 'string|nullable',
                'attributes.display_params' => 'string|nullable',
                'attributes.display' => 'string|nullable',
                'attributes.locked' => 'int',
                'attributes.rank' => 'int',
            ],
            default => []
        };
    }
}
