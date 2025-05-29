<?php

namespace Modules\Iuser\Services;

use Illuminate\Support\Facades\Auth;


class AuthService
{

    public function logUserIn($user)
    {
        Auth::login($user); //OJO: esto nose si sea necesario
        session()->regenerate();
    }


    public function logUserOut()
    {
        //Method Laravel\Passport\Guards\TokenGuard::logout does not exist.
        //Auth::logout();

        session()->invalidate();
        session()->regenerateToken();
    }

}
