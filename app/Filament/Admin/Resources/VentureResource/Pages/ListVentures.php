<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Filament\Admin\Resources\VentureResource;
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
      __('models/venture.resource.tabs.all') => Tab::make(),
      __('models/venture.resource.tabs.pending') => Tab::make()
        ->badge(Venture::query()->where('approval_state', ApprovalState::PENDING)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::PENDING)),
      __('models/venture.resource.tabs.approved') => Tab::make()
        ->badge(Venture::query()->where('approval_state', ApprovalState::APPROVED)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::APPROVED)),
      __('models/venture.resource.tabs.rejected') => Tab::make()
        ->badge(Venture::query()->where('approval_state', ApprovalState::REJECTED)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_state', ApprovalState::REJECTED)),
    ];
  }
}
