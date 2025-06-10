<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\AuthRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
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
     * @param AuthRequest $request
     *
     * @return JsonResource|JsonResponse
     */
    public function login(AuthRequest $request): JsonResource | JsonResponse
    {
        /** @var Guard $guard */
        $guard = auth(config('manager-api.guard.provider'));

        if (!$token = $guard->attempt($request->validated())) {
            throw ValidationException::withMessages([__('global.login_processor_unknown_user')]);
        }

        $guard->login($guard->user(), $request->boolean('remember'));

        return JsonResource::make([
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'access_token' => $token,
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
     * @return JsonResource
     */
    protected function loginForm(Request $request, LoginLayout $layout): JsonResource
    {
        $languages = $this->getLanguages();
        $language =
            empty($languages[config('global.manager_language')]) ? 'en' : config('global.manager_language');

        return JsonResource::make([
            'username' => '',
            'password' => '',
            'remember' => true,
        ])
            ->meta([
                'site_name' => config('global.site_name'),
                'version' => config('global.settings_version'),
                'language' => $language,
                'languages' => $languages,
            ])
            ->layout($layout->default());
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
     * @return JsonResource
     */
    public function refresh(): JsonResource
    {
        /** @var Guard $guard */
        $guard = auth(config('manager-api.guard.provider'));

        return JsonResource::make([
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'access_token' => $guard->refresh(),
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
     * @return JsonResource
     */
    public function forgot(Request $request): JsonResource
    {
        return JsonResource::make([]);
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
     * @return JsonResource
     */
    public function forgotForm(Request $request): JsonResource
    {
        return JsonResource::make([]);
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
