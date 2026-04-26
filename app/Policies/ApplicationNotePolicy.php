<?php

namespace App\Policies;

use App\Helpers\Util;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class ApplicationNotePolicy extends BasePolicy
{
    public static $name = 'ApplicationNote';

    public function viewAny(Model $user, ?Application $application = null)
    {
        if (! $application) {
            return parent::viewAny($user);
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            return $user->id === $application->jobListing->member_id;
        }

        return parent::viewAny($user);
    }

    public function view(?Model $user, ?ApplicationNote $note = null)
    {
        if (! $note) {
            return parent::view($user);
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            return $user->id === $note->application->jobListing->member_id;
        }

        return parent::view($user);
    }

    public function create(Model $user, ?Application $application = null)
    {
        if ($user instanceof Member && Util::isPanelActive('member') && $application) {
            return $user->id === $application->jobListing->member_id;
        }

        return false;
    }

    public function update(Model $user, ?ApplicationNote $note = null)
    {
        if (! $note) {
            return false;
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            if ($user->id === $note->application->jobListing->member_id) {
                return true;
            }

            return false;
        }

        return parent::update($user);
    }

    public function delete(Model $user, ?ApplicationNote $note = null)
    {
        if (! $note) {
            return false;
        }

        if ($user instanceof Member && Util::isPanelActive('member')) {
            if ($user->id === $note->application->jobListing->member_id) {
                return true;
            }

            return false;
        }

        return parent::delete($user);
    }
}
