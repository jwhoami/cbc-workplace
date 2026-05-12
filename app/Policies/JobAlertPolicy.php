<?php

declare(strict_types=1);

namespace App\Policies;

use App\Helpers\Util;
use App\Models\JobAlert;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class JobAlertPolicy extends BasePolicy
{
    public static $name = 'JobAlert';

    public function viewAny(Model $user)
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            return true;
        }

        return parent::viewAny($user);
    }

    public function view(?Model $user, ?JobAlert $alert = null)
    {
        if (! $alert) {
            return parent::view($user);
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            return $user->id === $alert->member_id;
        }

        return parent::view($user);
    }

    public function create(Model $user)
    {
        if ($user instanceof Member && Util::isPanelActive('member')) {
            return true;
        }

        return parent::create($user);
    }

    public function update(Model $user, ?JobAlert $alert = null)
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $alert) {
            return $user->id === $alert->member_id;
        }

        return parent::update($user);
    }

    public function delete(Model $user, ?JobAlert $alert = null)
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $alert) {
            return $user->id === $alert->member_id;
        }

        return parent::delete($user);
    }
}
