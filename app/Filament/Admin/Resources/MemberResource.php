<?php

namespace App\Filament\Admin\Resources;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Filament\Admin\Resources\MemberResource\Pages;
use App\Filament\Admin\Resources\MemberResource\RelationManagers;
use App\Helpers\Util;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
  protected static ?string $model = Member::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-group';

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
            Infolists\Components\ImageEntry::make('avatar')
              ->label(__('models/member.fields.avatar'))
              ->disk('avatars')
              ->size(50)
              ->circular()
              ->defaultImageUrl(fn (Member $record) => $record->getFilamentAvatarUrl()),
            Infolists\Components\TextEntry::make('type')
              ->label(__('models/member.fields.type')),
            Infolists\Components\TextEntry::make('name')
              ->label(__('models/member.fields.name')),
            Infolists\Components\TextEntry::make('email')
              ->label(__('models/member.fields.email')),
            Infolists\Components\TextEntry::make('social_medias')
              ->label(__('models/member.fields.social_medias'))
              ->listWithLineBreaks()
          ]),
        Infolists\Components\Section::make(__('models/member.resource.sections.membership.label'))
          ->columns(['md' => 2, 'lg' => 2])
          ->collapsible()
          ->collapsed()
          ->columnSpanFull()
          ->visible(fn (Member $record) => $record->canViewMembershipRequest())
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
              })
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
      ->columns([
        Tables\Columns\ImageColumn::make('avatar')
          ->label(__('models/member.fields.avatar'))
          ->circular()
          ->disk('avatars')
          ->defaultImageUrl(fn (Member $record) => $record->getFilamentAvatarUrl()),
        Tables\Columns\TextColumn::make('name')
          ->label(__('models/member.fields.name'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('email')
          ->label(__('models/member.fields.email'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('type')
          ->label(__('models/member.fields.type')),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('type')
          ->label(__('models/member.fields.type'))
          ->options(MemberType::class)
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\DeleteAction::make()
        ])
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
      //
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
