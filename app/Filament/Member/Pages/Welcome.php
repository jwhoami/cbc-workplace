<?php

namespace App\Filament\Member\Pages;

use App\Models\Text;
use Filament\Pages\SimplePage;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class Welcome extends SimplePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.member.pages.welcome';

    protected static ?string $title = "Bienvenido";

  protected ?string $maxWidth = "5xl";

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
    if (! $record) return "";

    return $record->content;
  }
}
