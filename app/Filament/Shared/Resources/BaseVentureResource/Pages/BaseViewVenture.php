<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use App\Actions\Admin\VentureApproval;
use App\Actions\Member\Duplicate;
use App\Actions\Member\ExtendValidity;
use App\Actions\Member\RequestVentureApproval;
use App\Enums\VentureApprovalState;
use App\Filament\Admin\Resources\VentureResource;
use App\Helpers\Util;
use App\Models\Category;
use App\Models\Config;
use App\Models\Member;
use App\Models\Venture;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class BaseViewVenture extends ViewRecord
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.back.label'))
        ->tooltip(__('common.actions.back.tooltip'))
        ->color('gray')
        ->url(static::$resource::getUrl('index')),
      Actions\EditAction::make()
        ->label(__('common.actions.edit.label'))
        ->tooltip(__('common.actions.edit.tooltip'))
        ->visible(function (Venture $record) {
          $panel = Filament::getCurrentPanel()->getId();
          if ($panel === "admin") {
            return true;
          }
          return $panel === "member" &&
            in_array($record->approval_state, [VentureApprovalState::UNDEFINED, VentureApprovalState::REJECTED]);
        })
        ->action(function (Venture $record, array $data) {
          $record->categories
            ->each(function (Category $category) use ($record) {
              $record->categories()->detach($category);
            });
          $categories = $data['category'] ?? [];
          unset($data['category']);
          $record->update($data);
          $record->save();
          foreach ($categories as $id) {
            $category = Category::find($id);
            $record->categories()->attach($category);
          }
          Util::filamentNotification("!OPERATION-SUCCESS");
        }),
//        ->requiresAuthorization('Member.editVenture'),
      Actions\Action::make('request-approval')
        ->label(__('actions/member.request-venture-approval.label'))
        ->requiresConfirmation()
        ->visible(function (Venture $record) {
          return Util::isPanelActive('member') &&
            in_array($record->approval_state, [VentureApprovalState::UNDEFINED, VentureApprovalState::PENDING, VentureApprovalState::REJECTED]);
        })
//        ->requiresAuthorization('Member.requestVentureApproval')
        ->action(function (Venture $record) {
          Util::run(fn() => RequestVentureApproval::run($record));
          Util::filamentNotification("!OPERATION-SUCCESS");
        }),
      Actions\Action::make('approve-venture-request')
        ->label(__('actions/admin.approve-venture-request.label'))
        ->modalWidth('md')
        //->authorize('respondApprovalRequest', $this->getRecord())
        ->action(function (Venture $record, array $data) {
          return Util::run(fn() => VentureApproval::run($record, $data));
        })
        ->visible(function (Venture $record) {
          return Util::isPanelActive('admin') && $record->approval_state === VentureApprovalState::PENDING;
        })
        ->form([
          Forms\Components\Radio::make('decision')
            ->label(__('actions/admin.membership-approval.form.decision'))
            ->required()
            ->inline()
            ->inlineLabel(false)
            ->options([
              VentureApprovalState::APPROVED->value => VentureApprovalState::APPROVED->getLabel(),
              VentureApprovalState::REJECTED->value => VentureApprovalState::REJECTED->getLabel(),
            ]),
          Forms\Components\Textarea::make('approval_reason')
            ->label(__('models/venture.fields.approval_reason'))
            ->requiredIf('decision', VentureApprovalState::REJECTED->value),
        ]),
      //Actions\Action::make('reject-venture-approval')
      //  ->label(__('actions/admin.reject-venture-approval.label'))
      //  ->modalWidth('md')
      //  ->color('danger')
      //  ->authorize('reject', $this->getRecord())
      //  ->action(function (Venture $record, array $data) {
      //      $data['decision'] = ApprovalState::REJECTED->value;

      //      Util::run(fn () => RespondVentureApprovalRequest::run($record, $data));
      //  })
      //  ->form([
      //    Forms\Components\Textarea::make('approval_reason')
      //      ->label(__('models/venture.fields.approval_reason'))
      //      ->required()
      //  ]),
      Actions\ActionGroup::make([
        Actions\Action::make('extend-validity')
          ->label(__('actions/member.extend-validity.label'))
          //->authorize('extendValidity', $this->getRecord())
          ->modalWidth('md')
          ->form([
            Forms\Components\DatePicker::make('date')
              ->label(__('models/venture.fields.expires_at'))
              ->required()
              ->helperText(function () {
                $maxDays = Config::make()->getp('ventures.validity.maxExtension');

                return __('actions/member.extend-validity.form.helper-text', ['days' => $maxDays]);
              })
              ->maxDate(now()->addDays(Config::make()->getp('ventures.validity.maxExtension'))),
          ])
          ->visible(function (Venture $record) {
            Util::isPanelActive('member') && $record->status === VentureApprovalState::APPROVED;
          })
//          ->requiresAuthorization('Member.extendVentureValidity')
          ->action(function (Venture $record, array $data) {
            Util::run(fn() => ExtendValidity::run($record, Carbon::parse($data['date'])));
          }),
        Actions\Action::make('duplicate')
          ->label(__('actions/member.duplicate.label'))
          ->requiresConfirmation()
          //->authorize('duplicate', $this->getRecord())
          ->visible(function (Venture $record) {
            return Util::isPanelActive('member');
          })
//          ->requiresAuthorization('Member.dupVenture')
          ->action(function (Venture $record) {
            $new = Util::run(fn() => Duplicate::run($record));

            return redirect(VentureResource::getUrl('edit', ['record' => $new]));
          }),
      ]),
    ];
  }
}
