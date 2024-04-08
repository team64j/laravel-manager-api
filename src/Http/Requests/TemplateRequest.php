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
                'templatename' => 'required|string',
                'templatealias' => 'string|nullable',
                'description' => 'string',
                'editor_type' => 'int',
                'category' => 'required|int',
                'template_type' => 'int',
                'content' => 'string|nullable',
                'locked' => 'int',
                'selectable' => 'int',
            ],
            default => []
        };
    }

    public function attributes(): array
    {
        return [
            'templatename' => '"' . Lang::get('global.template_name') . '"',
            'templatealias' => '"' . Lang::get('global.alias') . '"',
            'description' => '"' . Lang::get('global.template_desc') . '"',
            'content' => '"' . Lang::get('global.template_code') . '"',
        ];
    }
}
