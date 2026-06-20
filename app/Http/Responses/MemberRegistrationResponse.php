<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;

class MemberRegistrationResponse implements RegistrationResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $url = url(route('member-welcome'));
        //    $url = "/" . Filament::getPanel('member')->getPath();
        //    dd($url);

        return redirect()->to($url);
    }
}
