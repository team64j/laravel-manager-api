<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can(['new_role', 'edit_role', 'save_role', 'delete_role']);
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
