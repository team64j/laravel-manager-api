<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->canAny(['access_permissions']);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
