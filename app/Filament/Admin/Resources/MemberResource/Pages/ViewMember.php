<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Actions\Admin\MembershipApproval;
use App\Enums\MembershipState;
use App\Filament\Admin\Resources\MemberResource;
use App\Helpers\Util;
use App\Models\Member;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;

class ViewMember extends ViewRecord
{
  protected static string $resource = MemberResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list.label')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(MemberResource::getUrl('index')),
      Actions\Action::make('set-password')
        ->label(__('Fijar Contraseña'))
        ->tooltip(__('Fijar contraseña del afiliado'))
        ->color('gray')
        ->modalWidth('md')
        ->form([
          Forms\Components\TextInput::make('password')
            ->label(__('Contraseña'))
            ->required()
            ->password()
            ->same('password_confirmation')
            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
            ->dehydrated(fn(?string $state): bool => filled($state))
            ->required(fn(string $operation): bool => $operation === 'create'),
          Forms\Components\TextInput::make('password_confirmation')
            ->label(__('Confirmar Contraseña'))
            ->dehydrated(false)
            ->password(),
        ])
        ->action(function (Member $record, array $data) {
          $record->password = $data['password'];
          $record->save();
          Util::filamentNotification("!OPERATION-SUCCESS");
        }),
      Actions\Action::make('membership-approval')
        ->label(__('actions/admin.membership-approval.label'))
        ->modalWidth('md')
        ->authorize('approveMembershipRequest')
        ->visible(function (Member $record) {
          return $record->membership_state == MembershipState::PENDING;
        })
        ->form([
          Forms\Components\Radio::make('decision')
            ->label(__('actions/admin.membership-approval.form.decision'))
            ->required()
            ->inline()
            ->inlineLabel(false)
            ->options([
              MembershipState::APPROVED->value => MembershipState::APPROVED->getLabel(),
              MembershipState::REJECTED->value => MembershipState::REJECTED->getLabel(),
            ]),
          Forms\Components\Textarea::make('membership_approval_reason')
            ->label(__('models/member.fields.membership_approval_reason'))
            ->requiredIf('decision', MembershipState::REJECTED->value)
        ])
        ->action(function (Member $record, array $data) {
          Util::run(fn() => MembershipApproval::run($record, $data));
        }),
    ];
  }
}
