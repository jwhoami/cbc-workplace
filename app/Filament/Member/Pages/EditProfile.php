<?php

namespace App\Filament\Member\Pages;

use App\Helpers\Util;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as AuthEditProfile;
use Filament\Actions;
use App\Actions\Member\RequestMembership;
use App\Enums\MembershipState;

class EditProfile extends AuthEditProfile
{
  protected static ?string $navigationIcon = 'heroicon-o-document-text';

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('request-membership')
        ->label(__('actions/member.request-membership.label'))
        ->modalDescription(__('actions/member.request-membership.description'))
        ->modalWidth('xl')
        ->authorize('requestMembership', $this->getUser())
        ->action(function (array $data) {
          Util::run(fn () => RequestMembership::run($this->getUser(), $data));
        })
        ->form([
          Forms\Components\Textarea::make('reason')
            ->label(__('models/member.fields.membership_reason'))
            ->required()
            ->rows(5)
            ->maxLength(5000)
        ])
    ];
  }

  public static function isSimple(): bool
  {
    return false;
  }

  public function form(Form $form): Form
  {
    return $form
      ->inlineLabel(false)
      ->schema([
        Forms\Components\Grid::make()
          ->columns(['md' => 3, 'lg' => 3])
          ->schema([
            Forms\Components\Section::make()
              ->columns(2)
              ->columnSpan(['md' => 2])
              ->schema([
                Forms\Components\TextInput::make('name')
                  ->label(__('models/member.fields.name'))
                  ->maxLength(255),
                Forms\Components\TextInput::make('email')
                  ->label(__('models/member.fields.email'))
                  ->email()
                  ->maxLength(255),
                Forms\Components\Repeater::make('social_medias')
                  ->label(__('models/member.fields.social_medias'))
                  ->columnSpanFull()
                  ->simple(
                    Forms\Components\TextInput::make('name')
                      ->maxLength(255)
                  )
              ]),
            Forms\Components\Section::make()
              ->columnSpan(['md' => 1])
              ->columns(1)
              ->schema([
                Forms\Components\Placeholder::make('type')
                  ->label(__('models/member.fields.type'))
                  ->content(fn (Member $record) => $record->type->getLabel())
              ]),
          ]),
        Forms\Components\Section::make(__('models/member.resource.sections.membership.label'))
          ->description(function (Member $record) {
            return $record->membership_state === MembershipState::PENDING
              ? __('models/member.resource.sections.membership.description.waiting')
              : __('models/member.resource.sections.membership.description.returned');
          })
          ->columns(['md' => 2, 'lg' => 2])
          ->columnSpanFull()
          ->collapsible()
          ->visible(fn (Member $record) => $record->canViewMembershipRequest())
          ->collapsed(fn (Member $record) => $record->membership_state !== MembershipState::PENDING)
          ->schema([
            Forms\Components\Placeholder::make('membership_state')
              ->label(__('models/member.fields.membership_state'))
              ->columnSpanFull()
              ->content(function () {
                return $this->getUser()->membership_state->getLabel();
              }),
            Forms\Components\Placeholder::make('membership_reason')
              ->label(__('models/member.fields.membership_reason'))
              ->hint(__(''))
              ->content(function () {
                return $this->getUser()->membership_reason;
              }),
            Forms\Components\Placeholder::make('membership_approval_reason')
              ->label(__('models/member.fields.membership_approval_reason'))
              ->helperText(function (Member $record) {
                return $record->isMembershipApprovalRespondeOld()
                  ? __('models/member.profile.membership_approval_reason.tooltip.previous')
                  : __('models/member.profile.membership_approval_reason.tooltip.new');
              })
              ->content(function () {
                return $this->getUser()->membership_approval_reason;
              }),
          ]),
      ]);
  }
}
