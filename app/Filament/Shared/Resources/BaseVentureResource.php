<?php

namespace App\Filament\Shared\Resources;

use App\Actions\Admin\VentureToggleActive;
use App\Actions\Member\ExtendValidity;
use App\Enums\VentureApprovalState;
use App\Helpers\Util;
use App\Models\Category;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;

class BaseVentureResource extends Resource
{
  protected static ?string $model = Venture::class;

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
                Util::isPanelActive('venture') => 'full',
                default => 2
              })
              ->schema([
                Infolists\Components\Section::make()
                  ->hidden(Util::isPanelActive('venture'))
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
                      ->icon(function ($state) {
                        return match ($state) {
                          true => 'heroicon-o-x-circle',
                          false => 'heroicon-o-check-circle',
                          default => '',
                        };
                      })
                      ->color(function ($state) {
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
                    Infolists\Components\ImageEntry::make('file')
                      ->label(false)
                      ->height(function (Venture $record) {
                        if (! $record->file) {
                          return 0;
                        }
                        $image = Storage::disk('public')->path($record->file);
                        list($width, $height) = getimagesize($image);
                        return $height;
                      })
                      ->width(function (Venture $record) {
                        if (! $record->file) {
                          return 0;
                        }
                        $image = Storage::disk('public')->path($record->file);
                        list($width, $height) = getimagesize($image);
                        return $width;
                      })
                      ->columnSpanFull(),
                  ]),
              ]),
            Infolists\Components\Section::make(__('models/venture.resource.sections.approval.label'))
              ->hidden(fn() => Util::isPanelActive('venture'))
              ->columnSpan(['md' => 1, 'lg' => 1])
              ->description(fn(Venture $record) => match ($record->approval_state) {
                VentureApprovalState::APPROVAL => __('models/venture.resource.sections.approval.description.waiting'),
                VentureApprovalState::APPROVED, VentureApprovalState::REJECTED => __('models/venture.resource.sections.approval.description.returned'),
                default => '',
              })
              ->schema([
                Infolists\Components\TextEntry::make('approval_state')
                  ->label(__('models/venture.fields.approval_state')),
                Infolists\Components\TextEntry::make('id')
                  ->label(__('models/venture.fields.categories'))
                  ->formatStateUsing(function (Venture $record) {
                    $categories = $record->categories
                      ->map(function (Category $category) {
                        return $category->name;
                      })
                      ->toArray();
                    return new HtmlString(implode(", ", $categories));
                  }),
                Infolists\Components\TextEntry::make('approval_reason')
                  ->label(__('models/venture.fields.approval_reason'))
                  ->helperText(function (Venture $record) {
                    return $record->isApprovalReasonOld()
                      ? __('models/venture.resource.tooltips.approval_reason.old')
                      : __('models/venture.resource.tooltips.approval_reason.new');
                  }),
                Infolists\Components\TextEntry::make('view_count')
                  ->label(__('models/venture.fields.view_count')),
                Infolists\Components\TextEntry::make('favorite_count')
                  ->label(__('models/venture.fields.favorite_count')),
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
            SelectTree::make('category_id')
              ->label(__('Categoría'))
              ->required()
              ->relationship(
                relationship: 'categories',
                titleAttribute: 'name',
                parentAttribute: 'parent_id',
                modifyQueryUsing: fn(Builder $query) => $query->where('scope', "Venture")->orderBy('name', 'asc'),
                modifyChildQueryUsing: fn(Builder $query) => $query->orderBy('name', 'asc'),
              ),
            Forms\Components\TextInput::make('title')
              ->label(__('models/venture.fields.title'))
              ->required()
              ->maxLength(100),
            Cluster::make([])
              ->label(__('models/venture.fields.expires_at'))
              ->visibleOn(['create'])
              ->schema([
                Forms\Components\Select::make('expiration_type')
                  ->dehydrated(false)
                  ->required()
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
            Forms\Components\TextInput::make('url')
              ->label(__('URL'))
              ->maxLength(255),
            // Forms\Components\FileUpload::make('file')
            //   ->label(__('Imagen'))
            //   ->directory('ventures')
            //   ->image()
            //   ->maxSize(1024)
            //   ->helperText(__('Max. tamaño de imagen 1Mb. Dimensiones recomendadas 300 x 300 o 800 x 300 pixeles')),
          ]),
        Forms\Components\Section::make(__('models/venture.fields.content'))
          ->schema([
            Forms\Components\RichEditor::make('content')
              ->label(false)
              ->fileAttachmentsDisk('public')
              ->required()
              ->disableToolbarButtons([
                'attachFiles',
                'blockquote',
                'codeBlock',
                'strike',
              ])
              ->columnSpanFull(),
            Placeholder::make('note')
              ->hiddenLabel()
              ->visible(function (?Venture $record = null) {
                if (! $record) return false;
                return in_array($record->approval_state, [VentureApprovalState::APPROVED]);
              })
              ->content(new HtmlString('<div class="text-danger-600">Importante: Este emprendimiento fue aprobada. Si usted guarda este emprendimiento, se desactivará el emprendimiento y tendrá que solicitar la aprobación nuevamente.</div>')),
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
          ->limit(30)
          ->searchable(),
        Tables\Columns\TextColumn::make('view_count')
          ->label(__('models/venture.fields.view_count'))
          ->alignCenter(),
        Tables\Columns\TextColumn::make('favorite_count')
          ->label(__('models/venture.fields.favorite_count'))
          ->alignCenter(),
        Tables\Columns\TextColumn::make('approval_at')
          ->label(__('models/venture.fields.approval_at'))
          ->label(function () {
            if (Util::isPanelActive('venture')) {
              return __('models/venture.fields.published_at');
            } else {
              return __('models/venture.fields.approval_at');
            }
          })
          ->getStateUsing(function (Venture $record) {
            return $record->approval_at?->format('Y-m-d');
          }),
        Tables\Columns\TextColumn::make('member.name')
          ->label(function () {
            $panel = Filament::getCurrentPanel()?->getId();
            return match ($panel) {
              'venture' => __('models/venture.resource.table.published_by'),
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
          ->hidden(fn() => Util::isPanelActive('venture')),
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
          Tables\Actions\DeleteAction::make()
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
}
