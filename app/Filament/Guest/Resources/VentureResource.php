<?php

namespace App\Filament\Guest\Resources;

use App\Enums\VentureApprovalState;
use App\Filament\Guest\Resources\VentureResource\Pages;
use App\Filament\Guest\Resources\VentureResource\Pages\PreviewVenture;
use App\Helpers\Util;
use App\Models\Category;
use App\Models\Venture;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Route;

class VentureResource extends Resource
{
  protected static bool $shouldSkipAuthorization = true;

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
        Infolists\Components\Section::make(__("Contactenos"))
          ->collapsible()
          ->collapsed()
          ->columns(2)
          ->extraAttributes([
            'style' => 'background-color: #FFF5D7; color: #fff;'
          ])
          ->schema([
            Infolists\Components\TextEntry::make('member.contact.name')
              ->label(__("Nombre")),
            Infolists\Components\TextEntry::make('member.contact.email')
              ->label(__("Email")),
            Infolists\Components\TextEntry::make('member.contact.phone')
              ->label(__("Teléfono")),
            Infolists\Components\TextEntry::make('member.contact.mobile')
              ->label(__("Celular")),
            Infolists\Components\TextEntry::make('member.contact.address')
              ->label(__("Dirección"))
              ->columnSpanFull(),
          ]),
        Infolists\Components\Section::make()
          ->schema([
            Infolists\Components\TextEntry::make('title')
              ->label(false)
              ->columnSpanFull()
              ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
              ->alignCenter()
              ->extraAttributes([
                'class' => 'px-3',
              ])
              ->weight(FontWeight::Bold),
            Infolists\Components\TextEntry::make('url')
              ->label(false)
              ->alignCenter()
              ->columnSpanFull()
              ->visible(fn(Venture $record) => $record->url)
              ->extraAttributes([
                'class' => 'px-3',
              ])
              ->url(fn(Venture $record) => $record->url)
              ->openUrlInNewTab(),
            Infolists\Components\TextEntry::make('content')
              ->label(false)
              ->markdown()
              ->extraAttributes([
                'class' => 'p-3 text-justify',
              ])
              ->columnSpanFull(),

          ]),
        Infolists\Components\Section::make()
          ->schema([
            Infolists\Components\RepeatableEntry::make('media')
              ->hiddenLabel()
              ->schema([
                Infolists\Components\TextEntry::make('caption')
                  ->hiddenLabel()
                  ->size(Infolists\Components\TextEntry\TextEntrySize::Medium)
                  ->alignCenter(),
                Infolists\Components\ImageEntry::make('file')
                  ->hiddenLabel()
                  ->disk('public')
                  ->alignCenter()
                  ->width(fn() => request()->input('mobile') ? 280 : 640)
                  ->height(fn() => request()->input('mobile') ? 280 : 480),
              ]),

          ]),
        Infolists\Components\Section::make()
          ->schema([
            Infolists\Components\TextEntry::make('expires_at')
              ->label(false)
              ->alignStart()
              ->formatStateUsing(function (Venture $record) {
                $date = "";
                if ($record->expires_at) {
                  $date = date_format($record->expires_at, config('appx.dateTimeFormat.display.date'));
                }
                return __("Anuncio Vence") . ": " . $date;
              }),

          ]),
      ]);
  }

  public static function form(Forms\Form $form): Forms\Form
  {
    return $form;
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('created_at', 'desc')
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->label(__('models/venture.fields.title'))
          ->grow(true),
        Tables\Columns\TextColumn::make('approval_at')
          ->label(function () {
            if (Util::isPanelActive('guest')) {
              return __('Fecha Publicado');
            } else {
              return __('models/venture.fields.approval_at');
            }
          })
          ->getStateUsing(function (Venture $record) {
            if (Util::isPanelActive('guest')) {
              return $record->approval_at?->format('d M, Y');
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
          }),
      ])
      ->persistFiltersInSession()
      ->paginated([10, 20])
      ->filtersFormColumns(3)
      ->filters([
        Filter::make('title')
          ->form([
            Forms\Components\TextInput::make('title')
              ->required()
              ->alphaDash()
              ->maxLength(50)
              ->label(__('Título')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['title'],
                function (Builder $query, $title): Builder {
                  $title = htmlentities($title);
                  return $query->where('title', 'like', "%{$title}%");
                },
              );
          })
          ->indicateUsing(function (array $data): ?string {
            if (!$data['title']) {
              return null;
            }

            return __('Título') . " " . $data['title'];
          }),
        Filter::make('tree')
          ->form([
            SelectTree::make('categories')
              ->label(__('Categorías'))
              ->placeholder(__('Seleccione categorías'))
              ->relationship(
                relationship: 'categories',
                titleAttribute: 'name',
                parentAttribute: 'parent_id',
                modifyQueryUsing: function (Builder $query) {
                  $query
                    ->where('scope', "Venture")
                    ->where('child_count', ">", 0)
                    ->orderBy('name', 'asc');
                  return $query;
                },
                modifyChildQueryUsing: function (Builder $query) {
                  $query->orderBy('name', 'asc');
                  return $query;
                }
              )
              ->enableBranchNode(false)
              ->independent(false),
          ])
          ->query(function (Builder $query, array $data) {
            return $query->when($data['categories'], function ($query, $categories) {
              return $query->whereHas('categories', fn($query) => $query->whereIn('categories.id', $categories));
            });
          })
          ->indicateUsing(function (array $data): ?string {
            if (!$data['categories']) {
              return null;
            }
            return __('Categorías') . ': ' . implode(', ', Category::whereIn('id', $data['categories'])->get()->pluck('name')->toArray());
          }),
        SelectFilter::make('member')
          ->label(__('Publicado Por'))
          ->relationship('member', 'name')
      ], layout: FiltersLayout::Modal)
      ->actions([])
      ->bulkActions([]);
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
      'index' => Pages\ListVentures::route('/'),
      'view' => Pages\ViewVenture::route('/{record}'),
      'preview' => Pages\PreviewVenture::route('/{record}/preview'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    $canPreview = ! str(Route::getCurrentRoute()->getName())->contains("preview");
    $query = parent::getEloquentQuery()
      ->when($canPreview, function (Builder $query) {
        $query->active()
          ->where('approval_state', VentureApprovalState::APPROVED)
          ->where('expires_at', '>', now())
          ->where('is_expired', 0);
      });
    return $query;
  }

  public static function shouldRegisterNavigation(): bool
  {
    return false;
  }
}
