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
      Actions\Action::make('membership-approval')
        ->label(__('actions/admin.membership-approval.label'))
        ->modalWidth('md')
        ->visible(fn (Member $record) => $record->membership_state === MembershipState::PENDING)
        ->action(function (Member $record, array $data) {
          Util::run(fn () => MembershipApproval::run($record, $data));
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
    ];
  }
}
