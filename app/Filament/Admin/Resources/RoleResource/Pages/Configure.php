<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use App\Filament\Admin\Resources\RoleResource;
use App\Models\Role;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Configure extends Page
{
  public $selected = [];

  public $role;

  protected static string $resource = RoleResource::class;

  protected static string $view = 'filament.resources.role-resource.pages.configure';

  protected static ?string $title = 'Configurar';

  public function mount(Role $role)
  {
    $this->role = $role;
    $this->selected = $role->perm ?? [];
    static::$title .= " - " . $role->name;
  }

  public function save()
  {
    $this->role->perm = $this->filterSelection($this->selected);
    $this->role->save();
    Notification::make()
      ->title(__('Operación Exitosa'))
      ->success()
      ->send();
    // return redirect()->to($this->getRedirectUrl());
  }

  protected function getHeaderActions(): array
  {
    return [
      Action::make('save')
        ->label(__('Guardar'))
        ->action('save'),
      Action::make('cancel')
        ->label(__('Volver'))
        ->url($this->getRedirectUrl())
        ->color('gray'),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function getViewData(): array
  {
    $perms = config('permissions');
    return [
      'perms' => $perms,
    ];
  }

  protected function filterSelection($selected)
  {
    foreach ($selected as $k => $v) {
      if (!Str::contains($v, ['.'])) {
        unset($selected[$k]);
      }
    }
    return array_values($selected);
  }
}
