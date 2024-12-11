<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->can('edit_user'),
            'store' => auth()->user()->can('new_user'),
            'update' => auth()->user()->can('save_user'),
            'destroy' => auth()->user()->can('delete_user'),
            default => auth()->user()->canAny(['edit_user', 'new_user', 'save_user', 'delete_user']),
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
