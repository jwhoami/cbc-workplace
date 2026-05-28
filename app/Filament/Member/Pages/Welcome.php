<?php

namespace App\Filament\Member\Pages;

use App\Models\Text;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;

class Welcome extends SimplePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.member.pages.welcome';

    protected static ?string $title = 'Bienvenido';

    protected ?string $maxWidth = '5xl';

    public static function canAccess(): bool
    {
        return true;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function hasTopBar(): bool
    {
        return false;
    }

    public function getText()
    {
        $record = Text::query()
            ->latestText('bienvenida-afiliado')
            ->first();
        if (! $record) {
            return '';
        }

        return $record->content;
    }

    public function resendVerification(): void
    {
        $user = auth('member')->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            Notification::make()
                ->title('Enlace enviado')
                ->body('Hemos enviado un nuevo enlace de verificación a ' . $user->email)
                ->success()
                ->send();
        }
    }

    public function logout(): void
    {
        auth('member')->logout();
        session()->invalidate();
        session()->regenerateToken();

        redirect()->to(route('filament.member.auth.login'));
    }
}

