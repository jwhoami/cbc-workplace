<?php

namespace App\Filament\Shared\Resources;

use App\Actions\Admin\VentureToggleActive;
use App\Actions\Member\ExtendValidity;
use App\Enums\VentureApprovalState;
use App\Helpers\Util;
use App\Models\Venture;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use CodeWithDennis\FilamentSelectTree\SelectTree;

class BaseVentureResource extends Resource
{
  protected static ?string $model = Venture::class;

  public static ?string $navigationIcon = 'heroicon-o-light-bulb';

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
              ->columnSpan(fn() => match (true) {
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
                    Infolists\Components\TextEntry::make('url')
                      ->label(__('URL')),
                    Infolists\Components\TextEntry::make('expires_at')
                      ->label(__('models/venture.fields.expires_at'))
                      ->dateTime(config('appx.dateTimeFormat.display.date')),
                    Infolists\Components\IconEntry::make('is_expired')
                      ->label(__('models/venture.fields.is_expired'))
                      ->icon(function($state) {
                        return match ($state) {
                          true => 'heroicon-o-x-circle',
                          false => 'heroicon-o-check-circle',
                          default => '',
                        };
                      })
                      ->color(function($state) {
                        return match ($state) {
                          true => 'danger',
                          false => 'success',
                          default => '',
                        };
                      }),
                    Infolists\Components\IconEntry::make('is_active')
                      ->label(__('models/venture.fields.is_active')),
                    Infolists\Components\IconEntry::make('is_extendable')
                      ->label(__('models/venture.fields.is_extendable')),
                  ]),
                Infolists\Components\Section::make()
                  ->schema([
                    Infolists\Components\TextEntry::make('content')
                      ->label(false)
                      ->html()
                      ->columnSpanFull(),
                  ]),
              ]),
            Infolists\Components\Section::make(__('models/venture.resource.sections.approval.label'))
              ->hidden(fn() => Util::isPanelActive('guest'))
              ->columnSpan(['md' => 1, 'lg' => 1])
              ->description(fn(Venture $record) => match ($record->approval_state) {
                VentureApprovalState::PENDING => __('models/venture.resource.sections.approval.description.waiting'),
                VentureApprovalState::APPROVED, VentureApprovalState::REJECTED => __('models/venture.resource.sections.approval.description.returned'),
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
                  }),
              ]),
          ]),
      ]);
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->columns(['md' => 2, 'lg' => 2])
          ->schema([
            SelectTree::make('category')
              ->label(__('Categoría'))
              ->required()
              ->relationship('categories', 'name', 'parent_id'),
            Forms\Components\TextInput::make('title')
              ->label(__('models/venture.fields.title'))
              ->required()
              ->maxLength(100),
            Forms\Components\TextInput::make('url')
              ->label(__('URL'))
              ->maxLength(255),
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
                  ->visible(fn(Get $get) => match ($get('expiration_type')) {
                    'custom' => true,
                    default => false
                  }),
              ]),
          ]),
        Forms\Components\Section::make(__('models/venture.fields.content'))
          ->schema([
            Forms\Components\RichEditor::make('content')
              ->label(false)
              ->fileAttachmentsDisk('public')
              ->disableToolbarButtons([
                'attachFiles',
                'blockquote',
                'codeBlock',
                'strike',
              ])
              ->columnSpanFull(),
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
          ->searchable(),
        Tables\Columns\TextColumn::make('approval_at')
          ->label(__('models/venture.fields.approval_at'))
          ->label(function () {
            if (Util::isPanelActive('guest')) {
              return __('models/venture.fields.published_at');
            } else {
              return __('models/venture.fields.approval_at');
            }
          })
          ->getStateUsing(function (Venture $record) {
            if (Util::isPanelActive('guest')) {
              return $record->approval_at?->format('Y-m-d');
            } else {
              return $record->approval_at?->format('Y-m-d H:i:s');
            }
          }),
        Tables\Columns\TextColumn::make('member.name')
          ->label(function () {
            $panel = Filament::getCurrentPanel()?->getId();
            return match ($panel) {
              'guest' => __('models/venture.resource.table.published_by'),
              default => __('models/venture.fields.member_id')
            };
          })
          ->searchable()
          ->hidden(fn() => Util::isPanelActive('member')),
        Tables\Columns\IconColumn::make('is_extendable')
          ->label(__('models/venture.fields.is_extendable'))
          ->alignCenter()
          ->boolean(),
        Tables\Columns\IconColumn::make('is_expired')
          ->label(__('models/venture.fields.is_expired'))
          ->boolean()
          ->alignCenter()
          ->trueIcon('heroicon-o-x-circle')
          ->trueColor('danger')
          ->falseIcon('heroicon-o-check-circle')
          ->falseColor('success'),
        Tables\Columns\IconColumn::make('is_active')
          ->label(__('models/venture.fields.is_active'))
          ->alignCenter()
          ->boolean(),
        Tables\Columns\TextColumn::make('approval_state')
          ->label(__('models/venture.fields.approval_state'))
          ->hidden(fn() => Util::isPanelActive('guest')),
      ])
      ->filters([])
      ->actions([
        //Tables\Actions\EditAction::make()
        //      ->label(false),
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make(__('Activar/Inactivar'))
            ->icon('heroicon-o-chevron-right')
            ->visible(function (Venture $record) {
              return in_array($record->approval_state, [VentureApprovalState::APPROVED]);
            })
            ->modalWidth('sm')
            ->form([
              Forms\Components\Textarea::make('reason')
                ->label(__('common.fields.reason'))
                ->required(),
            ])
            ->action(function (Venture $record, $data) {
              return Util::run(fn() => VentureToggleActive::run($record, $data));
            }),
          Tables\Actions\Action::make(__('Extender'))
            ->requiresConfirmation()
            ->modalHeading(__('Extender por 90 días?'))
            ->icon('heroicon-o-chevron-right')
            ->visible(function (Venture $record) {
              return (in_array($record->approval_state, [VentureApprovalState::APPROVED]) && $record->is_expired && $record->is_extendable);
            })
            ->action(function (Venture $record) {
              return Util::run(fn() => ExtendValidity::run($record));
            }),
          Tables\Actions\ViewAction::make(),
          //Tables\Actions\DeleteAction::make()
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
      //
    ];
  }

  public static function shouldRegisterNavigation(): bool
  {
    return false;
  }
}
