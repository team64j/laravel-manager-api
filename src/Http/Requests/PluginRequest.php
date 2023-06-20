<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PluginRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_plugin'),
            'store' => Gate::check('new_plugin'),
            'update' => Gate::check('save_plugin'),
            'destroy' => Gate::check('delete_plugin'),
            default => Gate::any(['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin']),
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
