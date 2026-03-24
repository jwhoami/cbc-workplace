<?php

namespace App\Actions\Member;

use App\Mail\Member\VentureExpired;
use App\Models\Venture;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkVentureAsExpired
{
    use AsAction;

    public function handle(Venture $venture)
    {
        $venture->is_expired = 1;
        $venture->is_active = 0;
        $venture->save();
        Mail::to($venture->member)->send(new VentureExpired($venture));
    }
}
