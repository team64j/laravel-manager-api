<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Throwable;

class TemplateRequest extends FormRequest
{
    /**
     * @return bool
     * @throws Throwable
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'select' => true,
            'index' => auth()->user()->can('edit_template'),
            'store' => auth()->user()->can('new_template'),
            'update' => auth()->user()->can('save_template'),
            'destroy' => auth()->user()->can('delete_template'),
            default => auth()->user()->canAny(['edit_template', 'new_template', 'save_template', 'delete_template']),
        };
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'update', 'store' => [
                'attributes.templatename' => 'required|string',
                'attributes.templatealias' => 'string|nullable',
                'attributes.description' => 'string',
                'attributes.editor_type' => 'int',
                'attributes.category' => 'required|int',
                'attributes.template_type' => 'int',
                'attributes.content' => 'string|nullable',
                'attributes.locked' => 'int',
                'attributes.selectable' => 'int',
            ],
            default => []
        };
    }

    public function attributes(): array
    {
        return [
            'attributes.templatename' => '"' . __('global.template_name') . '"',
            'attributes.templatealias' => '"' . __('global.alias') . '"',
            'attributes.description' => '"' . __('global.template_desc') . '"',
            'attributes.content' => '"' . __('global.template_code') . '"',
            'attributes.category' => '"' . __('global.existing_category') . '"',
        ];
    }
}
