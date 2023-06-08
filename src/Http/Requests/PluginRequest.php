<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Team64j\LaravelManagerApi\Http\Controllers\PluginController;

class PluginRequest extends FormRequest
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
                Auth::user()->hasPermissionsOrFail(['edit_plugin']);
                break;

            case 'store':
                Auth::user()->hasPermissionsOrFail(['new_plugin']);
                break;

            case 'update':
                Auth::user()->hasPermissionsOrFail(['save_plugin']);
                break;

            case 'destroy':
                Auth::user()->hasPermissionsOrFail(['delete_plugin']);
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
//     * @return array
//     */
//    public static function getRoutes(): array
//    {
//        return [
//            [
//                'method' => 'get',
//                'uri' => 'sort',
//                'action' => [PluginController::class, 'sort'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'categories',
//                'action' => [PluginController::class, 'categories'],
//            ],
//            [
//                'method' => 'get',
//                'uri' => 'tree',
//                'action' => [PluginController::class, 'tree'],
//            ],
//        ];
//    }
}
