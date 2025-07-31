<?php

namespace App\Filament\Member\Pages;

use App\Actions\Member\Affiliate;
use App\Enums\MembershipState;
use App\Helpers\Util;
use App\Models\Config;
use App\Models\Member;
use App\Models\MemberContact;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Actions;

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
      'social' => $this->user->contact->social ?? null,
    ]);
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('back')
        ->label(__("Volver"))
        ->color('gray')
        ->url(url()->route('filament.member.pages.dashboard')),
      Actions\Action::make('request-membership')
        ->label(__('actions/member.request-membership.label'))
        ->disabled(fn($livewire) => $livewire->user->membership_state === MembershipState::APPROVED)
        ->requiresConfirmation()
        ->action(function (array $data) {
          /** @var Member $user */
          if (!$this->user->contact?->email) {
            Util::filamentNotification(__("Favor complete su datos de contacto"), "warning");
            return;
          }

          if (Affiliate::run($this->user)) {
            Util::filamentNotification("!OPERATION-SUCCESS");
            $this->redirect(url()->route('filament.member.resources.ventures.index'));
          }
        }),
      Actions\ActionGroup::make([])
        ->button()
        ->label(__('Opciones')),
    ];
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
            Forms\Components\Repeater::make('social')
              ->label(__("Redes Sociales"))
              ->columnSpanFull()
              ->schema([
                Forms\Components\Select::make('network')
                  ->options(Config::make()->getp('social.networks'))
                  ->required(),
                Forms\Components\TextInput::make('url')
                  ->required()
                  ->maxLength(255)
                  ->helperText(__("Coloque el url iniciando con https://")),
              ])
              ->columns(2)

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
    // dd($data);
    $this->user->contact()->updateOrCreate([], $data);
    Util::filamentNotification("!OPERATION-SUCCESS");
  }
}
