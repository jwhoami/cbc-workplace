<?php

namespace App\Filament\Admin\Resources;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Filament\Admin\Resources\MemberResource\Pages;
use App\Filament\Admin\Resources\MemberResource\RelationManagers\CommentsRelationManager;
use App\Helpers\Util;
use App\Mail\Member\ActiveFlagToggled;
use App\Mail\Member\SponsorFlagToggled;
use App\Models\Member;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class MemberResource extends Resource
{
  protected static ?string $model = Member::class;

  protected static ?string $navigationIcon = 'heroicon-o-chevron-right';

  protected static ?string $navigationGroup = 'Emprendimientos';

  public static function getModelLabel(): string
  {
    return __('models/member.label');
  }

  public static function getPluralLabel(): string
  {
    return __('models/member.plural-label');
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make()
          ->columns(['md' => 2, 'lg' => 2])
          ->columnSpanFull()
          ->schema([
            Infolists\Components\TextEntry::make('type')
              ->label(__('models/member.fields.type')),
            Infolists\Components\TextEntry::make('name')
              ->label(__('models/member.fields.name')),
            Infolists\Components\TextEntry::make('email')
              ->label(__('models/member.fields.email')),
            Infolists\Components\TextEntry::make('social_medias')
              ->label(__('models/member.fields.social_medias'))
              ->listWithLineBreaks(),
            Infolists\Components\TextEntry::make('invitation.sponsor.name')
              ->label(__('models/member.fields.sponsor')),
          ]),
        Infolists\Components\Section::make(__('models/member.resource.sections.membership.label'))
          ->columns(['md' => 2, 'lg' => 2])
          ->collapsible()
          ->collapsed(fn(Member $record) => $record->membership_state !== MembershipState::PENDING)
          ->columnSpanFull()
          ->visible(fn(Member $record) => $record->canViewMembershipRequest())
          ->schema([
            Infolists\Components\TextEntry::make('membership_state')
              ->label(__('models/member.fields.membership_state')),
            Infolists\Components\TextEntry::make('membership_approval_by')
              ->label(__('models/member.fields.membership_approval_by'))
              ->formatStateUsing(function (Member $record) {
                return Util::formatUserDateAction($record->membership_approval_by, $record->membership_approval_at);
              }),
            Infolists\Components\TextEntry::make('membership_reason')
              ->label(__('models/member.fields.membership_reason')),
            Infolists\Components\TextEntry::make('membership_approval_reason')
              ->label(__('models/member.fields.membership_approval_reason'))
              ->helperText(function (Member $record) {
                return $record->isMembershipApprovalRespondeOld()
                  ? __('models/member.profile.membership_approval_reason.tooltip.previous')
                  : __('models/member.profile.membership_approval_reason.tooltip.new');
              }),
          ]),
      ]);
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('name', 'asc')
      ->columns([
        //Tables\Columns\ImageColumn::make('avatar')
        //  ->label(__('models/member.fields.avatar'))
        //  ->circular()
        //  ->disk('avatars')
        //  ->defaultImageUrl(fn (Member $record) => $record->getFilamentAvatarUrl()),
        Tables\Columns\TextColumn::make('id')
          ->label(__('common.fields.id'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('name')
          ->label(__('models/member.fields.name'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('email')
          ->label(__('models/member.fields.email'))
          ->searchable()
          ->sortable(),
        Tables\Columns\IconColumn::make('can_sponsor')
          ->label(__('models/member.fields.can_sponsor'))
          ->boolean()
          ->alignCenter()
          ->hidden(fn() => Util::isPanelActive('member')),
        Tables\Columns\IconColumn::make('is_active')
          ->label(__('models/member.fields.is_active'))
          ->boolean()
          ->alignCenter()
          ->hidden(fn() => Util::isPanelActive('member')),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('type')
          ->label(__('models/member.fields.type'))
          ->options(MemberType::class),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('toggle-active')
            ->icon('heroicon-o-chevron-right')
            ->label(__('actions/member.toggle-active.label'))
            ->modalWidth('sm')
            ->form([
              Forms\Components\Textarea::make('reason')
                ->label(__('common.fields.reason'))
                ->required(),
            ])
            ->action(function (Member $record, $data) {
              $record->is_active = !$record->is_active;
              $record->save();
              $state = ($record->is_active) ? 'Activado' : 'Inactivado';
              $record->addComment("Usuario {$state}, Memo: {$data['reason']}");
              Mail::to($record)->send(new ActiveFlagToggled($record, $data));
              Util::filamentNotification('!OPERATION-SUCCESS');
            }),
          Tables\Actions\Action::make('toggle-can-sponsor')
            ->icon('heroicon-o-chevron-right')
            ->label(__('actions/member.toggle-can-sponsor.label'))
            ->modalWidth('sm')
            ->form([
              Forms\Components\Textarea::make('reason')
                ->label(__('common.fields.reason'))
                ->required(),
            ])
            ->action(function (Member $record, $data) {
              $record->can_sponsor = !$record->can_sponsor;
              $record->save();
              $state = ($record->is_active) ? 'Activado' : 'Inactivado';
              $record->addComment("Protrocinador {$state}, Memo: {$data['reason']}");
              Mail::to($record)->send(new SponsorFlagToggled($record, $data));
              Util::filamentNotification('!OPERATION-SUCCESS');
            }),
          Tables\Actions\Action::make('setPassword')
            ->icon('heroicon-o-key')
            ->label('Fijar Contraseña')
            ->modalWidth('sm')
            ->action(function (Member $record, array $data): void {
              $record->password = $data['password'];
              $record->save();
              Notification::make()->title(__('Operación Exitosa'))->success()->send();
            })
            ->visible(fn(Member $record): bool => auth()->user()->hasPermission($record, 'user.set-password'))
            ->form([
              TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->rule(Password::default())
                ->autocomplete('off')
                ->dehydrated(fn($state): bool => filled($state))
                ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                ->same('passwordConfirmation'),
              TextInput::make('passwordConfirmation')
                ->label('Confirmar Contraseña')
                ->password()
                ->revealable()
                ->required()
                ->dehydrated(false)
            ]),
          Tables\Actions\ViewAction::make(),
          Tables\Actions\DeleteAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      CommentsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListMembers::route('/'),
      'create' => Pages\CreateMember::route('/create'),
      'view' => Pages\ViewMember::route('/{record}'),
      'edit' => Pages\EditMember::route('/{record}/edit'),
    ];
  }
}
