<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Layouts\LoginLayout;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth",
     *     summary="Авторизация",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"username", "password"},
     *             properties={
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="remember",
     *                     type="boolean",
     *                     nullable=true,
     *                 ),
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $guard = auth(Config::get('manager-api.guard.provider'));

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $validator->validate();

        if (!$token = $guard->attempt($validator->validated())) {
            return response()->json([
                'message' => Lang::get('global.login_processor_unknown_user'),
            ], 422);
        }

        $guard->login($guard->user(), $request->boolean('remember'));

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth(Config::get('manager-api.guard.provider'))->factory()->getTTL() * 60,
                'user' => auth(Config::get('manager-api.guard.provider'))->user(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/auth",
     *     summary="Форма авторизации",
     *     tags={"Auth"},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param Request $request
     * @param LoginLayout $layout
     *
     * @return JsonResponse
     */
    protected function loginForm(Request $request, LoginLayout $layout): JsonResponse
    {
        $languages = $this->getLanguages();
        $language =
            empty($languages[Config::get('global.manager_language')]) ? 'en' : Config::get('global.manager_language');

        return response()->json([
            'data' => [
                'username' => '',
                'password' => '',
                'remember' => true,
            ],
            'layout' => $layout->default(),
            'meta' => [
                'site_name' => Config::get('global.site_name'),
                'version' => Config::get('global.settings_version'),
                'language' => $language,
                'languages' => $languages,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Обновление токена",
     *     tags={"Auth"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'data' => [
                'access_token' => auth(Config::get('manager-api.guard.provider'))->refresh(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/forgot",
     *     summary="Восстановление пароля",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email"},
     *             properties={
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                 ),
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgot(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/auth/forgot",
     *     summary="Форма Восстановление пароля",
     *     tags={"Auth"},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotForm(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * load languages and keys
     *
     * @return array
     */
    protected function getLanguages(): array
    {
        $languages = [
            'be' => 'Беларуская мова',
            'bg' => 'Български език',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'de' => 'Deutsch',
            'en' => 'English',
            'es' => 'Español',
            'he' => 'עברית ʿ',
            'ja' => '日本語',
            'fa' => 'فارسی',
            'fi' => 'Suomi',
            'fr' => 'Français',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'nn' => 'Nynorsk',
            'pl' => 'Język polski',
            'pt' => 'Português',
            'ru' => 'Русский',
            'sv' => 'Svenska',
            'uk' => 'Українська мова',
            'zh' => '中文',
        ];

        $path = dirname(__DIR__, 3);
        $lang_keys_select = [];
        $dir = dir($path . '/lang');
        while ($file = $dir->read()) {
            if (is_dir($path . '/lang/' . $file) && ($file != '.' && $file != '..')) {
                $lang_keys_select[$file] = $languages[$file] ?? $file;
            }
        }
        $dir->close();

        return $lang_keys_select;
    }
}
