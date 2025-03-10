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
      __('models/venture.resource.tabs.undefined') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::UNDEFINED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::UNDEFINED)),
      __('models/venture.resource.tabs.pending') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::PENDING)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::PENDING)),
      __('models/venture.resource.tabs.approved') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::APPROVED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::APPROVED)),
      __('models/venture.resource.tabs.rejected') => Tab::make()
        ->badge(Venture::query()->where('approval_state', VentureApprovalState::REJECTED)->count())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('approval_state', VentureApprovalState::REJECTED)),
    ];
  }
}
