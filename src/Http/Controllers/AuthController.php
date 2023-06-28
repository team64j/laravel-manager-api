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
use Team64j\LaravelManagerApi\Components\Button;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Template;

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
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        if ($request->isMethod('get')) {
            return $this->loginForm($request);
        }

        $guard = auth(Config::get('manager-api.guard.provider'));

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $validator->validate();

        if (!$token = $guard->attempt($validator->validated())) {
            return response()->json([
                'errors' => [
                    'username' => [],
                    'password' => [],
                ],
                'message' => Lang::get('global.login_processor_unknown_user'),
            ], 422);
        }

        $guard->login($guard->user(), $request->boolean('remember'));

        return response()->json([
            'data' => $this->createNewToken((string)$token),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function loginForm(Request $request)
    {
        return response()->json([
            'data' => [
                'username' => '',
                'password' => '',
                'remember' => null,
            ],
            'layout' => [
                Input::make('username')
                    ->setLabel(Lang::get('global.username'))
                    ->setInputClass('!bg-transparent input-lg'),
                Input::make('password')
                    ->setType('password')
                    ->setLabel(Lang::get('global.password'))
                    ->setInputClass('!bg-transparent input-lg'),
                Template::make('remember')
                    ->setClass('flex justify-between items-center')
                    ->setSlot([
                        Checkbox::make()
                            ->setLabel(Lang::get('global.remember_username'))
                            ->setClass('!mb-0')
                            ->setInputClass('input-lg'),
                        Input::make()
                            ->setType('button')
                            ->setValue(Lang::get('global.login_button'))
                            ->setInputClass('btn-green btn-lg whitespace-nowrap'),
                    ])
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Обновление токена",
     *     tags={"Auth"},
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
            'data' => $this->createNewToken(auth(Config::get('manager-api.guard.provider'))->refresh()),
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
     * @return JsonResponse
     */
    public function forgot(): JsonResponse
    {
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return array
     */
    protected function createNewToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth(Config::get('manager-api.guard.provider'))->factory()->getTTL() * 60,
            'user' => auth(Config::get('manager-api.guard.provider'))->user(),
        ];
    }
}
