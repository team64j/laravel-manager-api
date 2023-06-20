<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UserRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_user'),
            'store' => Gate::check('new_user'),
            'update' => Gate::check('save_user'),
            'destroy' => Gate::check('delete_user'),
            default => Gate::any(['edit_user', 'new_user', 'save_user', 'delete_user']),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
