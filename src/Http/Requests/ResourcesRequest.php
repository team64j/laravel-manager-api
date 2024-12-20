<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourcesRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('view_document');
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
