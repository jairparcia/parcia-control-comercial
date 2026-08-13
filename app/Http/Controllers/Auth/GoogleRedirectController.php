<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GoogleRedirectController extends Controller
{
    public function __invoke()
    {
        return Socialite::driver('google')->redirect();
    }
}
