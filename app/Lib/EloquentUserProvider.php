<?php

namespace App\Libs;

use Illuminate\Contracts\Auth\Authenticatable;


class EloquentUserProvider extends \Illuminate\Auth\EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $credentials['password'] == $user->getAuthPassword();
    }
}
