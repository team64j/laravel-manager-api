<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->can('edit_module'),
            'store' => auth()->user()->can('new_module'),
            'update' => auth()->user()->can('save_module'),
            'destroy' => auth()->user()->can('delete_module'),
            'exec' => auth()->user()->can('list_module'),
            'run' => auth()->user()->can('exec_module'),
            default => auth()->user()->canAny(['edit_module', 'new_module', 'save_module', 'delete_module']),
        };
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'update', 'store' => [
                'name'                => 'required|string',
                'description'         => 'string|nullable',
                'modulecode'          => 'string|nullable',
                'editor_type'         => 'int',
                'enable_resource'     => 'int',
                'enable_sharedparams' => 'int',
                'guid'                => 'string',
                'icon'                => 'string|nullable',
                'properties'          => 'string|nullable',
                'rank'                => 'int',
                'locked'              => 'int',
                'category'            => 'int',
                'disabled'            => 'int',
            ],
            default => []
        };
    }
}
