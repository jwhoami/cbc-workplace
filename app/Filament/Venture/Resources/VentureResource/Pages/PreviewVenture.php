<?php

namespace App\Filament\Venture\Resources\VentureResource\Pages;

use App\Filament\Venture\Resources\VentureResource;
use App\Helpers\Util;
use App\Models\Venture;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Request;

class PreviewVenture extends ViewRecord
{
    protected static string $resource = VentureResource::class;

    public $returnPanel = 'member';

    public ?array $data = [];

    public bool $previewModeMobile = false;

    public function getTitle(): string|Htmlable
    {
        $isMobile = (bool) request()->input('mobile');

        return ($isMobile) ? __('Vista Movil') : __('Vista Desktop');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview-mobile')
                ->label(__('Vista Movil'))
                ->visible(fn () => ! (bool) request()->input('mobile'))
                ->url(function (PreviewVenture $livewire, Venture $record) {
                    return url()->route('filament.app.resources.ventures.preview', [$record, 'mobile' => 1, 'panel' => $livewire->returnPanel]);
                }),
            Actions\Action::make('preview-mobile')
                ->label(__('Vista Desktop'))
                ->visible(fn () => (bool) request()->input('mobile'))
                ->url(function (PreviewVenture $livewire, Venture $record) {
                    return url()->route('filament.app.resources.ventures.preview', [$record, 'panel' => $livewire->returnPanel]);
                }),
            Actions\Action::make('back')
                ->label(__('common.actions.back.label'))
                ->tooltip(__('common.actions.back.tooltip'))
                ->color('gray')
                ->action(function (PreviewVenture $livewire) {
                    $url = str(VentureResource::getUrl('view', [$this->record]))->replace('app/ventures/', "{$this->returnPanel}/ventures/")->value();
                    redirect($url);
                }),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $isMobile = (bool) ((int) request()->input('mobile', 0));

        $this->record->load([
            'media' => function ($query) use ($isMobile) {
                $query->isActive()->where('is_mobile', $isMobile);
            },
        ]);

        // dd($this->record);
        $this->returnPanel = Request::input('panel', 'member');

        if (! $this->record->preview_until) {
            Util::filamentNotification(__('Vista previa esta deshabilitada'), 'warning');
            $this->redirect('/');

            return;
        }
        if (Carbon::now()->isAfter($this->record->preview_until)) {
            Util::filamentNotification(__('Vista previa esta deshabilitada'), 'warning');
            $this->redirect('/');

            return;
        }
    }
}
