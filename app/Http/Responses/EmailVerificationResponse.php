<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse as EmailVerificationResponseContract;

// http://localhost/member/email-verification/verify/10/8bb48ec9b997aeffe3a4a1c83d92431795a69d8f?expires=1723048296&signature=5e261bc905521c1842ace2d3df3f10048150685598a54103dab991b8359c39f0
class EmailVerificationResponse implements EmailVerificationResponseContract
{
    public function toResponse($request)
    {
        $panel = Filament::getCurrentPanel()->getId();

        return redirect(route("filament.{$panel}.pages.dashboard"));
    }
}
