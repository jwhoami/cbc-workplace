<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Actions\Sponsor;
use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Filament\Admin\Resources\MemberResource;
use App\Helpers\Util;
use App\Models\Member;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
        Action::make('sponsor')
          ->label(__('Invitar'))
          ->modalWidth('sm')
          ->form([
          Components\TextInput::make('name')
            ->label('Nombre')
            ->required(),
          Components\TextInput::make('email')
            ->label('Email')
            ->email()
            ->required(),
          ])
          ->action(function (array $data) {
              Sponsor::run($data);
              Util::filamentNotification('!OPERATION-SUCCESS');
          }),
    ];
    }

    public function getTabs(): array
    {
        return [
          __('models/member.resource.table.tabs.requests') => Tab::make()
            ->badge(Member::query()->where('membership_state', MembershipState::PENDING)->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_state', MembershipState::PENDING)),
          __('models/member.resource.table.tabs.visitors') => Tab::make()
            ->badge(Member::query()->where('type', MemberType::VISITOR)->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', MemberType::VISITOR)),
          __('models/member.resource.table.tabs.members') => Tab::make()
            ->badge(Member::query()->where('type', MemberType::MEMBER)->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', MemberType::MEMBER)),
        ];
    }
}
