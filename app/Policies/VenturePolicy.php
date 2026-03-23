<?php

namespace App\Policies;

use App\Enums\ApprovalState;
use App\Enums\MemberType;
use App\Enums\VentureApprovalState;
use App\Helpers\Util;
use App\Models\Member;
use App\Models\User;
use App\Models\Venture;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class VenturePolicy extends BasePolicy
{
  public static $name = "Venture";

  //    public function before(Model $user, string $ability): bool|null
  //    {
  //        //if ($user instanceof User && $user->isAdmin()) {
  //        //    return true;
  //        //}
  //
  //        return null;
  //    }
  //
  //    /**
  //     * Determine whether the user can view any models.
  //     */
  //    public function viewAny(?Model $user): bool
  //    {
  //        if ($user instanceof User && !$user->hasPermission(static::prefix("viewAny"))) {
  //            return false;
  //        }
  //        if (Util::isPanelActive('member') && $user->type !== MemberType::MEMBER) {
  //            return false;
  //        }
  //
  //        return true;
  //    }
  //
  //    /**
  //     * Determine whether the user can view the model.
  //     */
  //    public function view(Model|null $user, Venture $venture): bool
  //    {
  //        if (! $user) {
  //            return true;
  //        }
  //        if ($user instanceof User && !$user->hasPermission(static::prefix("view"))) {
  //            return false;
  //        }
  //
  //        return true;
  //    }
  //
  //    /**
  //     * Determine whether the user can create models.
  //     */
  //    public function create(Model $user): bool
  //    {
  //        dd(__FUNCTION__);
  //        $fp = Filament::getCurrentPanel()->getId() ?? '';
  //        dd($user);
  //        dd("HEY");
  //
  //        return true;
  //    }
  //
  //    /**
  //     * Determine whether the user can update the model.
  //     */
  //    public function update(Model $user, Venture $venture): bool
  //    {
  //        if (!($user instanceof Member)) {
  //            return false;
  //        }
  //        if ($user->id !== $venture->member_id) {
  //            return false;
  //        }
  //
  //        $validStates = [ApprovalState::NEW, ApprovalState::REJECTED];
  //        if (!in_array($venture->approval_state, $validStates)) {
  //            return false;
  //        }
  //
  //        return true;
  //    }
  //
  //    /**
  //     * Determine whether the user can delete the model.
  //     */
  //    public function delete(Model $user, Venture $venture): bool
  //    {
  //        if (!($user instanceof Member)) {
  //            return false;
  //        }
  //        if ($user->id !== $venture->member_id) {
  //            return false;
  //        }
  //
  //        return true;
  //    }
  //
  //    /**
  //     * Determine whether the user can delete any models.
  //     */
  //    public function deleteAny(Model $user): bool
  //    {
  //        if (!($user instanceof Member)) {
  //            return false;
  //        }
  //
  //        return true;
  //    }
  //
  //    public function duplicate(Model $user, Venture $venture): bool
  //    {
  //        if (!($user instanceof Member)) {
  //            return false;
  //        }
  //        if ($venture->approval_state !== ApprovalState::APPROVED) {
  //            return false;
  //        }
  //
  //        return true;
  //    }

  //    public function requestApproval(Model $user, Venture $venture): bool
  //    {
  //        if (!($user instanceof Member)) {
  //            return false;
  //        }
  //
  //        $validStates = [ApprovalState::NEW, ApprovalState::REJECTED];
  //        if (!in_array($venture->approval_state, $validStates)) {
  //            return false;
  //        }
  //
  //        return true;
  //    }

  //    public function respondApprovalRequest(Model $user, Venture $venture): bool
  //    {
  //        if (!($user instanceof User) || Util::isPanelActive('venture')) {
  //            return false;
  //        }
  //        if (!$user->hasPermission(static::prefix("approve"))) {
  //            return false;
  //        }
  //
  //        if ($venture->approval_state !== ApprovalState::APPROVAL) {
  //            return false;
  //        }
  //
  //        return true;
  //    }

  public function reject(Model $user, Venture $venture): bool
  {
    if (! (($user instanceof User) && $user->hasPermission(static::prefix()))) {
      return false;
    }

    if ($venture->approval_state !== VentureApprovalState::APPROVED) {
      return false;
    }

    return true;
  }

  //    public function extendValidity(Model $user, Venture $venture)
  //    {
  //        if ($user instanceof User || Util::isPanelActive('venture')) {
  //            return false;
  //        }
  //        if ($venture->approval_state !== ApprovalState::APPROVED) {
  //            return false;
  //        }
  //
  //        return true;
  //    }

  //    public static function prefix($name)
  //    {
  //        return ucfirst(static::$name) . ".{$name}";
  //    }
}
