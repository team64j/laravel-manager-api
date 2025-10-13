<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Laravel\Sanctum\HasApiTokens;
use Throwable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $username
 */
class User extends \Illuminate\Foundation\Auth\User implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public $timestamps = false;

    protected $rememberTokenName = 'access_token';

    protected $hidden = [
        'password',
        'cachepwd',
        'verified_key',
        'refresh_token',
        'access_token',
        'valid_to',
    ];

    protected $fillable = [
        'username',
        'password',
        'cachepwd',
        'verified_key',
        'refresh_token',
        'access_token',
        'valid_to',
    ];

    public function attributes(): HasOne
    {
        return $this->hasOne(UserAttribute::class, 'internalKey', 'id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class, 'user', 'id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = 'https://example.com/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
        //Notification::send($this, new ResetPasswordNotification());
    }

    public function routeNotificationForMail($driver, $notification = null): array
    {
        /** @var UserAttribute $attributes */
        $attributes = UserAttribute::query()
            ->firstWhere('internalKey', $this->getKey());

        return [$attributes->email => $this->username];
    }

    public function hasPermissions($permissions): bool
    {
        return $this['attributes']
                ->rolePermissions
                ->whereIn('permission', (array) $permissions)
                ->count() == count($permissions);
    }

    /**
     * @throws Throwable
     */
    public function hasPermissionsOrFail($permissions): bool
    {
        return throw_unless($this->hasPermissions($permissions), Lang::get('global.error_no_privileges'));
    }

    public function hasPermission(Permissions $permission): bool
    {
        return (bool) $this['attributes']
            ->rolePermissions
            ->where('permission', $permission->key)
            ->count();
    }

    /**
     * @throws Throwable
     */
    public function hasPermissionOrFail(Permissions $permission): bool
    {
        return throw_unless($this->hasPermission($permission), Lang::get('global.error_no_privileges'));
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function isAdmin(): bool
    {
        return Auth::check() && $this['attributes']->userRole->name === 'Administrator';
    }
}
