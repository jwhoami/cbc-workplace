<?php

namespace App\Actions;

use App\Mail\Sponsor as MailSponsor;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class Sponsor
{
  use AsAction;

  public function handle(array $data)
  {
    $user = auth()->user();
    $invitation = $user->sponsor()->create([
      'expires_at' => now()->addDays(3),
    ]);

    Mail::to([['name' => $data['name'], 'email' => $data['email']]])
      ->send(new MailSponsor($user, $invitation, $data));
  }
}
