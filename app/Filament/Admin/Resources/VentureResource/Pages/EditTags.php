<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Filament\Admin\Resources\VentureResource;
use App\Helpers\Util;
use App\Models\Venture;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions;

class EditTags extends Page implements HasForms
{
  use InteractsWithForms;

  public ?Venture $record;

  public ?array $data = [];

  protected static string $resource = VentureResource::class;

  protected static string $view = 'filament.member.resources.venture-resource.pages.edit-tags';

  public function mount(string|int|Venture $record): void
  {
    $this->record = $record;
    $this->form->fill([
      'tags' => $record->tags ?? [],
    ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->model($this->record)
      ->schema([
        Forms\Components\TagsInput::make('tags')
          ->label(__('models/venture.fields.tags')),
      ])
      ->statePath('data');
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('cancel')
        ->label(__('common.actions.cancel.label'))
        ->color('gray')
        ->url(VentureResource::getUrl('view', [$this->record])),
    ];
  }

  public function save(): void
  {
    $this->record->updateTags($this->data['tags'] ?? []);
    Util::filamentNotification("!OPERATION-SUCCESS");
  }
}
