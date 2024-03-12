<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use App\Actions\Admin\RespondVentureApprovalRequest;
use App\Actions\Member\Duplicate;
use App\Actions\Member\RequestVentureApproval;
use App\Enums\ApprovalState;
use App\Filament\Admin\Resources\VentureResource;
use App\Helpers\Util;
use App\Models\Venture;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class BaseViewVenture extends ViewRecord
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(static::$resource::getUrl('index')),
      Actions\EditAction::make()
        ->label(__('common.actions.edit.label'))
        ->tooltip(__('common.actions.edit.tooltip')),
      Actions\Action::make('duplicate')
        ->label(__('actions/member.duplicate.label'))
        ->requiresConfirmation()
        ->authorize('duplicate', $this->getRecord())
        ->action(function (Venture $record) {
          $new =  Util::run(fn () => Duplicate::run($record));

          return redirect(VentureResource::getUrl('edit', ['record' => $new]));
        }),
      Actions\Action::make('request-approval')
        ->label(__('actions/member.request-venture-approval.label'))
        ->requiresConfirmation()
        ->authorize('requestApproval', $this->getRecord())
        ->action(function (Venture $record) {
          return Util::run(fn () => RequestVentureApproval::run($record));
        }),
      Actions\Action::make('respond-venture-approval-request')
        ->label(__('actions/admin.respond-venture-approval-request.label'))
        ->modalWidth('md')
        ->authorize('respondApprovalRequest', $this->getRecord())
        ->action(function (Venture $record, array $data) {
          Util::run(fn () => RespondVentureApprovalRequest::run($record, $data));
        })
        ->form([
          Forms\Components\Radio::make('decision')
            ->label(__('actions/admin.membership-approval.form.decision'))
            ->required()
            ->inline()
            ->inlineLabel(false)
            ->options([
              ApprovalState::APPROVED->value => ApprovalState::APPROVED->getLabel(),
              ApprovalState::REJECTED->value => ApprovalState::REJECTED->getLabel(),
            ]),
          Forms\Components\Textarea::make('approval_reason')
            ->label(__('models/venture.fields.approval_reason'))
            ->requiredIf('decision', ApprovalState::REJECTED->value)
        ]),
      Actions\Action::make('reject-venture-approval')
        ->label(__('actions/admin.reject-venture-approval.label'))
        ->modalWidth('md')
        ->color('danger')
        ->authorize('reject', $this->getRecord())
        ->action(function (Venture $record, array $data) {
          $data['decision'] = ApprovalState::REJECTED->value;

          Util::run(fn () => RespondVentureApprovalRequest::run($record, $data));
        })
        ->form([
          Forms\Components\Textarea::make('approval_reason')
            ->label(__('models/venture.fields.approval_reason'))
            ->required()
        ])
    ];
  }
}
