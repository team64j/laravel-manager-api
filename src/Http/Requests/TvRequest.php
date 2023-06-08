<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TvRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        switch ($this->route()->getActionMethod()) {
            case 'index':
            case 'show':
            case 'list':
            case 'tree':
                Auth::user()->hasPermissionsOrFail(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
                break;

            case 'store':
                Auth::user()->hasPermissionsOrFail(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
                break;

            case 'update':
                Auth::user()->hasPermissionsOrFail(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
                break;

            case 'destroy':
                Auth::user()->hasPermissionsOrFail(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']);
                break;
        }

        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
