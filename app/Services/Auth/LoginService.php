<?php

namespace App\Services\Auth;

class LoginService
{
    public function execute($credentials)
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new \Exception('Unauthorized', 500);
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user(),
        ];

    }
}
