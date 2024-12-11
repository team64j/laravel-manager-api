<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can(['frames', 'home']);
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
