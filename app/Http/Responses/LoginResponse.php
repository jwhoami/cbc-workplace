<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $url = '/'.Filament::getCurrentPanel()->getPath();

        if (session()->has('login_redirect')) {
            $redirect = session()->pull('login_redirect');
            if (str_starts_with($redirect, url('/')) || str_starts_with($redirect, '/')) {
                return redirect($redirect);
            }
        }

        return redirect($url);
    }
}
