<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventLogRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'destroy' => auth()->user()->can('delete_eventlog'),
            default => auth()->user()->canAny(['view_eventlog']),
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
