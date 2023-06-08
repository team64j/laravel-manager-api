<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChunkRequest extends FormRequest
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
                Auth::user()->hasPermissionsOrFail(['edit_chunk']);
                break;

            case 'store':
                Auth::user()->hasPermissionsOrFail(['new_chunk']);
                break;

            case 'update':
                Auth::user()->hasPermissionsOrFail(['save_chunk']);
                break;

            case 'destroy':
                Auth::user()->hasPermissionsOrFail(['delete_chunk']);
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

//    /**
//     * @return array[]
//     */
//    public static function getRoutes(): array
//    {
//        return [
//            [
//                'method' => 'get',
//                'uri' => 'categories',
//                'action' => [ChunkController::class, 'categories'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'tree',
//                'action' => [ChunkController::class, 'tree'],
//            ],
//        ];
//    }
}
