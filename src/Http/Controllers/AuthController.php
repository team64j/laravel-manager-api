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
            'data' => $this->createNewToken((string) $token),
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
