<?php

namespace App\Policies;

use App\Enums\ApprovalState;
use App\Helpers\Util;
use App\Models\Member;
use App\Models\User;
use App\Models\Venture;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class VenturePolicy
{
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(?Model $user): bool
  {
    return true;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(?Model $user, Venture $venture): bool
  {
    return true;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(Model $user): bool
  {
    if (!($user instanceof Member)) return false;

    return true;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(Model $user, Venture $venture): bool
  {
    if (!($user instanceof Member)) return false;
    if ($user->id !== $venture->member_id) return false;

    $validStates = [ApprovalState::UNDEFINED, ApprovalState::REJECTED];
    if (!in_array($venture->approval_state, $validStates)) return false;

    return true;
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(Model $user, Venture $venture): bool
  {
    if (!($user instanceof Member)) return false;
    if ($user->id !== $venture->member_id) return false;

    return true;
  }

  /**
   * Determine whether the user can delete any models.
   */
  public function deleteAny(Model $user): bool
  {
    if (!($user instanceof Member)) return false;

    return true;
  }

  public function duplicate(Model $user, Venture $venture): bool
  {
    if (!($user instanceof Member)) return false;
    if ($venture->approval_state !== ApprovalState::APPROVED) return false;

    return true;
  }

  public function requestApproval(Model $user, Venture $venture): bool
  {
    if (!($user instanceof Member)) return false;

    $validStates = [ApprovalState::UNDEFINED, ApprovalState::REJECTED];
    if (!in_array($venture->approval_state, $validStates)) return false;

    return true;
  }

  public function respondApprovalRequest(Model $user, Venture $venture): bool
  {
    if (!($user instanceof User) || Util::isPanelActive('guest')) return false;

    if ($venture->approval_state !== ApprovalState::PENDING) return false;

    return true;
  }

  public function reject(Model $user, Venture $venture): bool
  {
    if (!($user instanceof User) || Util::isPanelActive('guest')) return false;

    if ($venture->approval_state !== ApprovalState::APPROVED) return false;

    return true;
  }
}
