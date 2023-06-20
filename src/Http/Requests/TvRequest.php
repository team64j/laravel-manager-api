<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TvRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::check(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
