<?php

namespace App\Filament\Member\Pages;

use App\Helpers\Util;
use App\Models\Member;
use App\Models\MemberContact;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Pages\Concerns\InteractsWithFormActions;

class Contact extends Page implements HasForms
{
  use InteractsWithForms, InteractsWithFormActions;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';

  protected static string $view = 'filament.member.pages.contact';

  public ?array $data = [];

  public Member $user;

  public function mount(): void
  {
    $this->user = auth()->guard('member')->user();
    $this->form->fill([
      'name' => $this->user->contact->name ?? "",
      'email' => $this->user->contact->email ?? "",
      'phone' => $this->user->contact->phone ?? "",
      'mobile' => $this->user->contact->mobile ?? "",
      'address' => $this->user->contact->address ?? "",
      'location' => $this->user->contact->location ?? "",
    ]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->columns(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label(__("Nombre Completo"))
              ->required(),
            Forms\Components\TextInput::make('email')
              ->label(__("Email"))
              ->email()
              ->required(),
            Forms\Components\TextInput::make('phone')
              ->label(__("Teléfono")),
            Forms\Components\TextInput::make('mobile')
              ->label(__("Celular")),
            Forms\Components\TextInput::make('address')
              ->label(__("Dirección"))
              ->columnSpanFull(),
            // Forms\Components\TextInput::make('location')
            //   ->label(__("Ubicación")),
          ]),
      ])
      ->statePath('data');
  }

  protected function getFormActions(): array
  {
    return [
      Action::make('submit')
        ->label(__("Guardar"))
        ->submit('submit'),
    ];
  }

  public function submit(): void
  {
    $data = $this->form->getState();
    $this->user->contact()->updateOrCreate([], $data);
    Util::filamentNotification("!OPERATION-SUCCESS");
  }
}
