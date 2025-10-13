<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('logs');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
