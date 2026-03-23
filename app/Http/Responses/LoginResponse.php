<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
  /**
   * Create an HTTP response that represents the object.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function toResponse($request)
  {
    $url = "/" . Filament::getCurrentPanel()->getPath();
//    dd($url);

//    return redirect()->intended($url);
    return redirect($url);
  }
}
