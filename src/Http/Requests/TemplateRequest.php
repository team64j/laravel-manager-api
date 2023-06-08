<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return throw_unless(
            match ($this->route()->getActionMethod()) {
                'index', 'show', 'list', 'tree', 'tvs' => Auth::user()->can('edit_template'),
                'store' => Auth::user()->can('new_template'),
                'update' => Auth::user()->can('save_template'),
                'destroy' => Auth::user()->can('delete_template'),
                default => true,
            },
            AuthorizationException::class,
            Lang::get('global.error_no_privileges')
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        if (in_array($this->route()->getActionMethod(), ['index', 'show', 'list', 'tree', 'select', 'tvs'])) {
            return [];
        }

        return [
            'templatename' => 'required|string',
            'templatealias' => 'string|nullable',
            'description' => 'string',
            'editor_type' => 'int',
            'category' => 'int',
            'template_type' => 'int',
            'content' => 'string|nullable',
            'locked' => 'int',
            'selectable' => 'int',
        ];
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
