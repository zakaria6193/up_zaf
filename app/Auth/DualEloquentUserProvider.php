<?php

namespace App\Auth;

use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class DualEloquentUserProvider extends EloquentUserProvider
{
    public const SESSION_KEY = 'auth_user_model';

    /**
     * @var array<int, class-string<Authenticatable>>
     */
    private const ALLOWED_MODELS = [
        User::class,
        BusinessUser::class,
    ];

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $modelClass = $this->resolveModelClass();

        return $modelClass::query()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * When the session does not yet know which model was used (cold remember-me),
     * try both models and match the remember token so ID collisions cannot confuse auth.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        if (session()->has(self::SESSION_KEY)) {
            return $this->matchRememberToken($this->resolveModelClass(), $identifier, $token);
        }

        foreach (self::ALLOWED_MODELS as $modelClass) {
            $user = $this->matchRememberToken($modelClass, $identifier, $token);

            if ($user !== null) {
                session([self::SESSION_KEY => $modelClass]);

                return $user;
            }
        }

        return null;
    }

    /**
     * @param  class-string<Authenticatable>  $modelClass
     */
    private function matchRememberToken(string $modelClass, mixed $identifier, string $token): ?Authenticatable
    {
        $user = $modelClass::query()->find($identifier);

        if (! $user) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $user
            : null;
    }

    /**
     * @return class-string<Authenticatable>
     */
    private function resolveModelClass(): string
    {
        $modelClass = session(self::SESSION_KEY);

        if (! is_string($modelClass) || ! in_array($modelClass, self::ALLOWED_MODELS, true)) {
            return User::class;
        }

        return $modelClass;
    }
}
