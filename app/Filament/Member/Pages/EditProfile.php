<?php

namespace App\Filament\Member\Pages;

use App\Actions\Member\RequestAffiliation;
use App\Enums\MembershipState;
use App\Helpers\Util;
use App\Mail\Member\ProfileUpdated;
use App\Mail\Member\SponsorFlagToggled;
use App\Models\Member;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as AuthEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

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
//        ->hasAuthorization('Member.requestAffiliation')
//        ->requiresAuthorization('Member.requestAffiliation')
        ->hidden(function () {
          return auth()->user()->membership_state === MembershipState::APPROVED;
        })
        ->action(function (array $data) {
          /** @var Member $user */
          $user = $this->getUser();
          Util::run(fn () => RequestAffiliation::run($user, $data));
        })
        ->form([
          Forms\Components\Textarea::make('reason')
            ->label(__('models/member.fields.membership_reason'))
            ->required()
            ->rows(5)
            ->maxLength(5000),
        ]),
      Actions\ActionGroup::make([
      ])
      ->button()
      ->label(__('Opciones')),
    ];
  }

  public static function isSimple(): bool
  {
    return false;
  }

  public function afterSave()
  {
    /** @var Member $record*/
    $record = $this->getUser();
    Mail::to($record)->send(new ProfileUpdated($record));
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
                TextInput::make('password')
                  ->label('Contraseña')
                  ->password()
                  ->revealable()
                  ->rule(Password::default())
                  ->autocomplete('off')
                  ->required(fn (string $operation): bool => $operation === 'create')
                  ->dehydrated(fn ($state): bool => filled($state))
                  ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                  ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                  ->label('Confirmar Contraseña')
                  ->password()
                  ->revealable()
                  ->dehydrated(false),
                Forms\Components\Repeater::make('social_medias')
                  ->label(__('models/member.fields.social_medias'))
                  ->columnSpanFull()
                  ->simple(
                    Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                  ),
              ]),
            Forms\Components\Section::make()
              ->columnSpan(['md' => 1])
              ->columns(1)
              ->schema([
                Forms\Components\Placeholder::make('type')
                  ->label(__('models/member.fields.type'))
                  ->content(fn (Member $record) => $record->type->getLabel()),
                Forms\Components\Placeholder::make('sponsor')
                  ->label(__('models/member.fields.sponsor'))
                  ->content(fn (Member $record) => $record->invitation->sponsor->name),
                Forms\Components\Placeholder::make('registered_at')
                  ->label(__('models/member.fields.created_at'))
                  ->content(fn (Member $record) => $record->created_at->format('Y-m-d H:i:s')),
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
                $user = $this->getUser();
                return $user->membership_state->getLabel().'@'.$user->membership_approval_at?->format('Y-m-d H:i:s');
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
