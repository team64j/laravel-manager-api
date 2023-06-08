<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Team64j\LaravelManagerApi\Http\Controllers\UserController;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
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
//                'uri' => 'list',
//                'action' => [UserController::class, 'list'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'active',
//                'action' => [UserController::class, 'getActive'],
//            ],
//        ];
//    }
}
