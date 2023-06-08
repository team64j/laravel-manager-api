<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @var array
     */
    protected array $routeOptions = [
        'only' => '',
    ];

    protected array $routes = [
        [
            'method' => 'post',
            'uri' => '/',
            'action' => [self::class, 'login'],
            'middleware' => [],
        ],
    ];

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('manager.auth:manager', [
            'except' => ['login', 'logout', 'auth', 'store'],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth('manager')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        auth('manager')->login(auth('manager')->user(), $request->boolean('remember'));

        return $this->createNewToken((string) $token);
    }

    /**
     * @param Request $request
     *
     * @return Application|JsonResponse|RedirectResponse|Redirector
     */
    public function logout(Request $request): JsonResponse|Redirector|Application|RedirectResponse
    {
        if ($request->isMethod('get')) {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(route('manager.login'));
        }

        auth('manager')->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->createNewToken(auth('manager')->refresh());
    }

    /**
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        return response()->json(auth('manager')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function createNewToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('manager')->factory()->getTTL() * 60,
            'user' => auth('manager')->user(),
            'redirect' => url('manager'),
        ]);
    }
}
