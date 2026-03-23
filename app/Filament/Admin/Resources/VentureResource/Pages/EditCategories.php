<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Filament\Admin\Resources\VentureResource;
use App\Helpers\Util;
use App\Models\Venture;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

class EditCategories extends Page implements HasForms
{
  use InteractsWithForms;

  public ?Venture $record;

  public ?array $data = [];

  protected static string $resource = VentureResource::class;

  protected static string $view = 'filament.member.resources.venture-resource.pages.edit-categories';

  public function mount(string|int|Venture $record): void
  {
    $this->record = $record;
    $this->form->fill([
      'categories' => $record->categories,
    ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->model($this->record)
      ->schema([
        SelectTree::make('categories')
          ->label(__('Categoría'))
          ->required()
          ->relationship(
            relationship: 'categories',
            titleAttribute: 'name',
            parentAttribute: 'parent_id',
            modifyQueryUsing: fn(Builder $query) => $query->where('scope', "Venture")->orderBy('name', 'asc'),
            modifyChildQueryUsing: fn(Builder $query) => $query->orderBy('name', 'asc'),
          ),
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
    $categories = $this->data['categories'] ?? [];
    $this->record->updateCategories($categories);
    Util::filamentNotification("!OPERATION-SUCCESS");
  }
}
