<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
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
            'index' => Gate::check('edit_template'),
            'store' => Gate::check('new_template'),
            'update' => Gate::check('save_template'),
            'destroy' => Gate::check('delete_template'),
            default => Gate::any(['edit_template', 'new_template', 'save_template', 'delete_template']),
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
            'attributes.templatename' => '"' . Lang::get('global.template_name') . '"',
            'attributes.templatealias' => '"' . Lang::get('global.alias') . '"',
            'attributes.description' => '"' . Lang::get('global.template_desc') . '"',
            'attributes.content' => '"' . Lang::get('global.template_code') . '"',
        ];
    }
}
