<?php

namespace App\Filament\Shared\Resources;

use App\Enums\ApprovalState;
use App\Helpers\Util;
use App\Models\Venture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;

class BaseVentureResource extends Resource
{
  protected static ?string $model = Venture::class;

  static ?string $navigationIcon = 'heroicon-o-light-bulb';

  public static function getModelLabel(): string
  {
    return __('models/venture.label');
  }

  public static function getPluralModelLabel(): string
  {
    return __('models/venture.plural-label');
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Grid::make()
          ->columns(['md' => 3, 'lg' => 3])
          ->schema([
            Infolists\Components\Group::make()
              ->columnSpan(fn () => match (true) {
                Util::isPanelActive('guest') => 'full',
                default => 2
              })
              ->schema([
                Infolists\Components\Section::make()
                  ->hidden(Util::isPanelActive('guest'))
                  ->columns(2)
                  ->schema([
                    Infolists\Components\TextEntry::make('title')
                      ->label(__('models/venture.fields.title')),
                    Infolists\Components\TextEntry::make('expires_at')
                      ->label(__('models/venture.fields.expires_at'))
                      ->dateTime(config('appx.dateTimeFormat.display.date'))
                  ]),
                Infolists\Components\Section::make(__('models/venture.fields.content'))
                  ->schema([
                    Infolists\Components\TextEntry::make('content')
                      ->label(false)
                      ->markdown()
                      ->columnSpanFull()
                  ]),
              ]),
            Infolists\Components\Section::make(__('models/venture.resource.sections.approval.label'))
              ->hidden(fn () => Util::isPanelActive('guest'))
              ->columnSpan(['md' => 1, 'lg' => 1])
              ->description(fn (Venture $record) => match ($record->approval_state) {
                ApprovalState::PENDING => __('models/venture.resource.sections.approval.description.waiting'),
                ApprovalState::APPROVED, ApprovalState::REJECTED => __('models/venture.resource.sections.approval.description.returned'),
                default => '',
              })
              ->schema([
                Infolists\Components\TextEntry::make('approval_state')
                  ->label(__('models/venture.fields.approval_state')),
                Infolists\Components\TextEntry::make('approval_reason')
                  ->label(__('models/venture.fields.approval_reason'))
                  ->helperText(function (Venture $record) {
                    return $record->isApprovalReasonOld()
                      ? __('models/venture.resource.tooltips.approval_reason.old')
                      : __('models/venture.resource.tooltips.approval_reason.new');
                  })
              ])
          ])
      ]);
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->columns(['md' => 2, 'lg' => 2])
          ->schema([
            Forms\Components\TextInput::make('title')
              ->label(__('models/venture.fields.title'))
              ->required()
              ->maxLength(100),
            Cluster::make([])
              ->label(__('models/venture.fields.expires_at'))
              ->schema([
                Forms\Components\Select::make('expiration_type')
                  ->dehydrated(false)
                  ->live()
                  ->options([
                    'default' => __('models/venture.resource.form.expiration-type.default'),
                    'custom' => __('models/venture.resource.form.expiration-type.custom'),
                  ]),
                Forms\Components\DatePicker::make('expires_at')
                  ->visible(fn (Get $get) => match ($get('expiration_type')) {
                    'custom' => true,
                    default => false
                  })
              ])
          ]),
        Forms\Components\Section::make(__('models/venture.fields.content'))
          ->schema([
            Forms\Components\MarkdownEditor::make('content')
              ->label(false)
              ->fileAttachmentsDisk('public')
              ->columnSpanFull()
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('created_at', 'desc')
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->label(__('models/venture.fields.title'))
          ->grow(true)
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('member.name')
          ->label(__('models/venture.fields.member_id'))
          ->sortable()
          ->searchable()
          ->hidden(fn () => Util::isPanelActive('member')),
        Tables\Columns\TextColumn::make('approval_state')
          ->label(__('models/venture.fields.approval_state'))
          ->hidden(fn () => Util::isPanelActive('guest'))
      ])
      ->filters([])
      ->actions([
        Tables\Actions\EditAction::make()
          ->label(false),
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
}
