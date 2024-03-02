<?php

namespace App\Filament\Member\Pages;

use App\Enums\MemberType;
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
        ->visible(fn () => $this->getUser()->membership_state === MembershipState::VISITOR)
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
            Forms\Components\Group::make()
              ->columnSpan(['md' => 1])
              ->schema([
                Forms\Components\FileUpload::make('avatar')
                  ->label(__('models/member.fields.avatar'))
                  ->disk('avatars')
                  ->image()
                  ->avatar()
                  ->imageEditor()
                  ->circleCropper(),
                Forms\Components\Section::make()
                  ->columns(1)
                  ->schema([
                    Forms\Components\Placeholder::make('type')
                      ->label(__('models/member.fields.type'))
                      ->content(fn (Member $record) => $record->type->getLabel())
                  ]),
              ]),
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
          ])
      ]);
  }
}
