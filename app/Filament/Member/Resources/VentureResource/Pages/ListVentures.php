<?php

namespace App\Filament\Member\Resources\VentureResource\Pages;

use App\Filament\Member\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseListVentures;
use App\Enums\ApprovalState;
use App\Models\Venture;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVentures extends BaseListVentures
{
  protected static string $resource = VentureResource::class;

  public function getTabs(): array
  {
    return [
      __('models/venture.resource.tabs.undefined') => Tab::make()
        ->badge($this->getCountOfApprovalState(ApprovalState::UNDEFINED))
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::UNDEFINED)),
      __('models/venture.resource.tabs.pending') => Tab::make()
        ->badge($this->getCountOfApprovalState(ApprovalState::PENDING))
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::PENDING)),
      __('models/venture.resource.tabs.approved') => Tab::make()
        ->badge($this->getCountOfApprovalState(ApprovalState::APPROVED))
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::APPROVED)),
      __('models/venture.resource.tabs.rejected') => Tab::make()
        ->badge($this->getCountOfApprovalState(ApprovalState::REJECTED))
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::REJECTED)),
    ];
  }

  protected function getCountOfApprovalState(ApprovalState $state)
  {
    return Venture::ofMember(auth()->user())->where('approval_state', $state)->count();
  }
}
