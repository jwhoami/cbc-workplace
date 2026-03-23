<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class MediaPolicy extends BasePolicy
{
  public static $name = "Media";
}
