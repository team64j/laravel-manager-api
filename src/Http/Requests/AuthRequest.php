<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public ?string $token = null;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

//    /**
//     * @return void
//     *
//     * @throws ValidationException
//     */
//    public function authenticate(): void
//    {
//        $this->ensureIsNotRateLimited();
//
//        if (!Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
//            RateLimiter::hit($this->throttleKey());
//
//            throw ValidationException::withMessages([
//                'username' => trans('auth.failed'),
//            ]);
//        }
//
//        RateLimiter::clear($this->throttleKey());
//    }
//
//    /**
//     * @return void
//     *
//     * @throws ValidationException
//     */
//    public function ensureIsNotRateLimited(): void
//    {
//        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
//            return;
//        }
//
//        event(new Lockout($this));
//
//        $seconds = RateLimiter::availableIn($this->throttleKey());
//
//        throw ValidationException::withMessages([
//            'email' => trans('auth.throttle', [
//                'seconds' => $seconds,
//                'minutes' => ceil($seconds / 60),
//            ]),
//        ]);
//    }
//
//    /**
//     * @return string
//     */
//    public function throttleKey(): string
//    {
//        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
//    }
}
