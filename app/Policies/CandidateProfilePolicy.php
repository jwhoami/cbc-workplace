<?php

namespace App\Policies;

use App\Models\CandidateProfile;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class CandidateProfilePolicy extends BasePolicy
{
    public static $name = 'CandidateProfile';

    public function update(Model $user, ?CandidateProfile $candidateProfile = null)
    {
        if ($user instanceof Member && $candidateProfile) {
            return $user->id === $candidateProfile->member_id;
        }

        return $user->hasPermission(static::prefix());
    }
}
