<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Team64j\LaravelManagerApi\Http\Controllers\CategoryController;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        switch ($this->route()->getActionMethod()) {
            case 'index':
            case 'show':
            case 'list':
            case 'tree':
                Auth::user()->hasPermissionsOrFail(['category_manager']);
                break;

            case 'store':
                Auth::user()->hasPermissionsOrFail(['category_manager']);
                break;

            case 'update':
                Auth::user()->hasPermissionsOrFail(['category_manager']);
                break;

            case 'destroy':
                Auth::user()->hasPermissionsOrFail(['category_manager']);
                break;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

//    /**
//     * @return array
//     */
//    public static function getRoutes(): array
//    {
//        return [
//            [
//                'method' => 'get',
//                'uri' => 'sort',
//                'action' => [CategoryController::class, 'sort'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'select',
//                'action' => [CategoryController::class, 'select'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'categories',
//                'action' => [CategoryController::class, 'categories'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'tree',
//                'action' => [CategoryController::class, 'tree'],
//            ],
//        ];
//    }
}
