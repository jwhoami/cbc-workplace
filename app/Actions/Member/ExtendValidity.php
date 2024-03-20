<?php

namespace App\Actions\Member;

use App\Models\Venture;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ExtendValidity
{
  use AsAction;

  public function handle(Venture $venture, Carbon $date)
  {
    $venture->expires_at = $date;
    $venture->save();
  }
}
