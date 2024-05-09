<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ModuleRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_module'),
            'store' => Gate::check('new_module'),
            'update' => Gate::check('save_module'),
            'destroy' => Gate::check('delete_module'),
            'exec' => Gate::check('list_module'),
            'run' => Gate::check('exec_module'),
            default => Gate::any(['edit_module', 'new_module', 'save_module', 'delete_module']),
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
