<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Filament\Admin\Resources\MemberResource;
use App\Models\Member;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMembers extends ListRecords
{
  protected static string $resource = MemberResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }

  public function getTabs(): array
  {
    return [
      __('models/member.resource.table.tabs.visitors') => Tab::make()
        ->badge(Member::query()->where('type', MemberType::VISITOR)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('type', MemberType::VISITOR)),
      __('models/member.resource.table.tabs.members') => Tab::make()
        ->badge(Member::query()->where('type', MemberType::MEMBER)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('type', MemberType::MEMBER)),
      __('models/member.resource.table.tabs.requests') => Tab::make()
        ->badge(Member::query()->where('membership_state', MembershipState::PENDING)->count())
        ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_state', MembershipState::PENDING)),
    ];
  }
}
