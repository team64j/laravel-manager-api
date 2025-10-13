<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->can('edit_plugin'),
            'store' => auth()->user()->can('new_plugin'),
            'update' => auth()->user()->can('save_plugin'),
            'destroy' => auth()->user()->can('delete_plugin'),
            default => auth()->user()->canAny(['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin']),
        };
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store', 'update' => [
                'name'        => 'required|string',
                //'description' => 'string',
                'editor_type' => 'int',
                'category'    => 'required|int',
                'plugincode'  => 'string|nullable',
                'locked'      => 'int',
                'disabled'    => 'int',
                //'cache_type' => 'int',
                'properties'  => 'string|nullable',
            ],
            default => []
        };
    }
}
