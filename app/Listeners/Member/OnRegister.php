<?php

namespace App\Listeners\Member;

use App\Mail\Member\Registered as MailMemberRegistered;
use Filament\Events\Auth\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class OnRegister
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $member = $event->getUser();
        Mail::to($member)->send(new MailMemberRegistered($member));
    }
}
