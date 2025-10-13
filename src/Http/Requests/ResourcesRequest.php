<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('view_document');
    }

    public function rules(): array
    {
        return [];
    }
}
