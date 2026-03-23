<?php

namespace App\Filament\Member\Resources\VentureResource\RelationManagers;

use App\Enums\VentureApprovalState;
use App\Helpers\Util;
use App\Models\Config;
use App\Models\Media;
use App\Models\Venture;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class MediaRelationManager extends RelationManager
{

  protected static ?string $pluralModelLabel = "Medios";

  protected static string $relationship = 'media';

  protected static bool $shouldSkipAuthorization = true;

  public static function getTitle(Model $ownerRecord, string $pageClass): string
  {
    return __('Imagenes');
  }

  public function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->columns(1)
      ->schema([
        Infolists\Components\TextEntry::make('caption')
          ->label(__("Título")),
        Infolists\Components\TextEntry::make('url')
          ->label(__("Enlace")),
        Infolists\Components\ImageEntry::make('file')
          ->label(__("Imagen"))
          ->disk('public'),
      ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->columns(1)
      ->schema([
        Forms\Components\TextInput::make('caption')
          ->label(__("Nombre"))
          ->required()
          ->maxLength(255),
        // Forms\Components\Toggle::make('is_mobile')
        //   ->label(__("Para Movil"))
        //   ->live(),
        // Forms\Components\FileUpload::make('file')
        //   ->label(__("Archivo"))
        //   ->required()
        //   ->directory('ventures')
        //   ->image()
        //   ->imageResizeMode('cover')
        //   ->imageCropAspectRatio('1:1')
        //   ->imageResizeTargetWidth('300')
        //   ->imageResizeTargetHeight('300')
        //   ->imageEditor()
        //   ->maxSize(1000)
        //   ->maxFiles(3)
        //   ->panelLayout('grid')
        //   ->helperText(function (Get $get) {
        //     return new HtmlString("<span class='text-s'>Tamaño max: 1Mb, Imagenes tipo jpg o png</span>");
        //   }),
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('caption')
      ->columns([
        Tables\Columns\TextColumn::make('caption')
          ->label(__("Título")),
        Tables\Columns\IconColumn::make('is_mobile')
          ->label(__("Para Movil"))
          ->boolean()
          ->alignCenter(),
        Tables\Columns\ToggleColumn::make('is_active')
          ->label(__("Activo"))
          ->disabled(function (MediaRelationManager $livewire) {
            $record = $livewire->getOwnerRecord();
            if (in_array($record->approval_state, [VentureApprovalState::NEW , VentureApprovalState::REJECTED])) {
              return false;
            }
            if (in_array($record->approval_state, [VentureApprovalState::APPROVAL])) {
              return true;
            }
            if (in_array($record->approval_state, [VentureApprovalState::APPROVED]) && !$record->is_active) {
              return true;
            }
            return false;
          }),
      ])
      ->filters([
        //
      ])
      ->headerActions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('add-for-mobile')
            ->label(__("Imagen para movil"))
            ->icon('heroicon-o-chevron-right')
            ->modalWidth('md')
            ->visible(function (MediaRelationManager $livewire) {
              $record = $livewire->getOwnerRecord();
              if (in_array($record->approval_state, [VentureApprovalState::APPROVAL, VentureApprovalState::APPROVED]))
                return false;
              $max = Config::make()->getp('affiliateImageGallery.max', 1);
              $imageCount = $record->media()->where('is_mobile', true)->count();
              return ($imageCount < $max);
            })
            ->form(fn(MediaRelationManager $livewire): array => $livewire->formSchema('mobile'))
            ->action(function (MediaRelationManager $livewire, array $data) {
              $data['is_mobile'] = true;
              $livewire->getOwnerRecord()->media()->create($data);
              Util::filamentNotification("!OPERATION-SUCCESS");
            }),
          // ->authorize('Media.create', Media::class),
          Tables\Actions\Action::make('add-for-desktop')
            ->label(__("Imagen para desktop"))
            ->icon('heroicon-o-chevron-right')
            ->modalWidth('md')
            ->visible(function (MediaRelationManager $livewire) {
              $record = $livewire->getOwnerRecord();
              if (in_array($record->approval_state, [VentureApprovalState::APPROVAL, VentureApprovalState::APPROVED]))
                return false;
              $max = Config::make()->getp('affiliateImageGallery.max', 1);
              $imageCount = $record->media()->where('is_mobile', false)->count();
              return ($imageCount < $max);
            })
            ->form(fn(MediaRelationManager $livewire): array => $livewire->formSchema('desktop'))
            ->action(function (MediaRelationManager $livewire, array $data) {
              $livewire->getOwnerRecord()->media()->create($data);
              Util::filamentNotification("!OPERATION-SUCCESS");
            }),
          // ->authorize('Media.create', Media::class),
        ])
          ->label("Aregar")
          ->button(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make()
          ->hiddenLabel(),
        Tables\Actions\EditAction::make()
          ->hiddenLabel()
          ->modalWidth('md')
          ->visible(function (MediaRelationManager $livewire) {
            $record = $livewire->getOwnerRecord();
            return in_array($record->approval_state, [VentureApprovalState::NEW , VentureApprovalState::UPDATED, VentureApprovalState::REJECTED]);
          }),
        Tables\Actions\DeleteAction::make()
          ->hiddenLabel()
          ->visible(function (MediaRelationManager $livewire) {
            $record = $livewire->getOwnerRecord();

            if (in_array($record->approval_state, [VentureApprovalState::APPROVED]) && !$record->is_active) {
              return false;
            }
            return !in_array($record->approval_state, [VentureApprovalState::APPROVAL]);
          }),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make()
            ->visible(function (MediaRelationManager $livewire) {
              $record = $livewire->getOwnerRecord();
              if ($record->approval_state != 0)
                return false;
            }),
        ]),
      ]);
  }

  public function isReadOnly(): bool
  {
    return false;
  }

  protected function formSchema($for): array
  {
    $form = [
      Forms\Components\TextInput::make('caption')
        ->label(__("Título"))
        ->required()
        ->maxLength(255),
    ];

    if ($for == "mobile") {
      array_push($form, Forms\Components\FileUpload::make('file')
        ->label(__("Archivo"))
        ->required()
        ->directory('ventures')
        ->image()
        // ->imageResizeMode('cover')
        // ->imageCropAspectRatio('1:1')
        // ->imageResizeTargetWidth('300')
        // ->imageResizeTargetHeight('300')
        // ->imageEditor()
        ->maxSize(250)
        ->rules([
          'dimensions:max_width=300'
        ])
        ->validationMessages([
          'dimensions' => __('La imagen excede las dimensiones permitidas'),
        ])
        ->helperText(function (Get $get) {
          return new HtmlString("<span class='text-s'>Tamaño max: 250Kb, Imagenes tipo jpg o png, ancho que no exceda 300px</span>");
        }));
    } else {
      array_push($form, Forms\Components\FileUpload::make('file')
        ->label(__("Archivo"))
        ->required()
        ->directory('ventures')
        ->image()
        // ->imageResizeMode('cover')
        // ->imageCropAspectRatio('4:3')
        // ->imageResizeTargetWidth('640')
        // ->imageResizeTargetHeight('480')
        // ->imageEditor()
        ->maxSize(500)
        ->rules([
          'dimensions:max_width=640'
        ])
        ->validationMessages([
          'dimensions' => __('La imagen excede las dimensiones permitidas'),
        ])
        ->helperText(function (Get $get) {
          return new HtmlString("<span class='text-s'>Tamaño max: 500Kb, Imagenes tipo jpg o png, ancho que no exceda 640px</span>");
        }));
    }
    return $form;
  }
}
