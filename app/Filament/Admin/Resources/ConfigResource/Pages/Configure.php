<?php

namespace App\Filament\Admin\Resources\ConfigResource\Pages;

use App\Filament\Admin\Resources\ConfigResource;
use App\Helpers\Util;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use JerryWhoami\FilamentFormComponents\Forms\JsonEditor;

class Configure extends EditRecord
{
    public $jsondata = [];

    protected static string $resource = ConfigResource::class;

    protected $backupToFile = 'app_config';

    protected static ?string $title = 'Configuración';

    public function mount($record): void
    {
        parent::mount($record);
        abort_if(! auth('admin')->user()->hasPermission('Config.configure'), 401);
        $this->jsondata = json_encode($this->record->jsondata);
        // dd(config('app_config.notifications'));
        // Notification::route('mail', 'aa@change.com')
        //   ->notify(new NotifyStaffComplaintCreated(Complaint::find(1)));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['jsondata'] = json_encode($data['jsondata']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['jsondata'] = json_decode($data['jsondata']);

        return $data;
    }

    public function restoreJsonData()
    {
        $this->record->jsondata = $this->record->jsonbkup;
        $this->record->save();
        Util::filamentNotification(message: '!OPERATION-SUCCESS');
    }

    public function backupJsonData()
    {
        $this->record->jsonbkup = $this->record->jsondata;
        $this->record->save();
        Util::filamentNotification(message: '!OPERATION-SUCCESS');
    }

    public function restoreJsonDataFromFile()
    {
        $config = require base_path("config/{$this->backupToFile}.php");
        if (! is_array($config)) {
            Util::filamentNotification('Archivo no tiene configuración', 'warning');

            return;
        }
        $this->record->jsondata = $config;
        $this->record->save();
        Util::filamentNotification(message: '!OPERATION-SUCCESS');
    }

    public function backupJsonDataToFile()
    {
        $jsondata = $this->record->jsondata ?? [];
        $file = base_path('config/'.$this->backupToFile.'.php');
        $encoded = Util::varexport($jsondata, true);
        $content = "<?php \n\n return ".$encoded."\n?>";
        file_put_contents($file, $content);
        Util::filamentNotification(message: '!OPERATION-SUCCESS');
    }

    protected function getActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('backup-to-file')
                    ->label(__('Respaldar a Archivo'))
                    ->action('backupJsonDataToFile'),
                Actions\Action::make('restore-from-file')
                    ->label(__('Restaurar de Archivo'))
                    ->action('restoreJsonDataFromFile'),
                Actions\Action::make('backup')
                    ->label(__('Respaldar'))
                    ->action('backupJsonData'),
                Actions\Action::make('restore')
                    ->label(__('Restaurar'))
                    ->action('restoreJsonData'),
            ]),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->model($this->getRecord())
                ->schema($this->getFormSchema())
                ->statePath('data'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->columns(1)
                ->schema([
                    JsonEditor::make('jsondata')
                        ->height(400)
                        ->label(false),
                ]),
        ];
    }
}
