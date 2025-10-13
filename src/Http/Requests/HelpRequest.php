<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HelpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('help');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
