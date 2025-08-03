<?php

namespace App\Filament\Member\Resources\VentureResource\Pages;

use App\Enums\MembershipState;
use App\Filament\Member\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseEditVenture;
use App\Helpers\Util;
use App\Models\Category;
use Filament\Facades\Filament;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class EditVenture extends BaseEditVenture
{
  protected static string $resource = VentureResource::class;

  public function mount(int|string $record): void
  {
    parent::mount($record);
    if (filament()->auth()->user()->membership_state !== MembershipState::APPROVED) {
      Util::filamentNotification(__('Usted debe afiliarse para poder publicar su emprendimientos'), 'warning');
      $this->redirect('/member/profile');
    }
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('preview')
        ->label(__('actions/member.preview.label'))
        //->authorize('preview', $this->getRecord())
        ->action(function ($livewire) {
          redirect($livewire->preview());
        }),

    ];
  }

  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    // $record->categories
    //   ->each(function (Category $category) use ($record) {
    //     $record->categories()->detach($category);
    //   });
    // $categories = $data['category'] ?? [];
    // unset($data['category']);
    // $record->update($data);
    // foreach ($categories as $id) {
    //   $category = Category::find($id);
    //   $record->categories()->attach($category);
    // }

    $record->resetApproval();
    $record->update($data);

    return $record;
  }

  public function preview(): string
  {
    $this->record->preview_until = now()->addSeconds(300);
    $this->record->save();
    $url = route('venture-home') . "/ventures/{$this->record->id}/preview";
    return $url;
  }

  public function getRelationManagers(): array
  {
    return [];
  }
}
