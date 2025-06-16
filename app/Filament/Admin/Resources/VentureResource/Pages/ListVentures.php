<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Enums\VentureApprovalState;
use App\Filament\Admin\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseListVentures;
use App\Models\Venture;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVentures extends BaseListVentures
{
  protected static string $resource = VentureResource::class;

  public function getTabs(): array
  {
    return [
      __('models/venture.resource.tabs.all') => Tab::make(),
      __('models/venture.resource.tabs.new') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::NEW)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::NEW)),
      __('models/venture.resource.tabs.updated') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::UPDATED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::UPDATED)),
      __('models/venture.resource.tabs.approval') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::APPROVAL)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::APPROVAL)),
      __('models/venture.resource.tabs.approved') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::APPROVED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::APPROVED)),
      __('models/venture.resource.tabs.rejected') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::REJECTED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::REJECTED)),
    ];
  }
}
