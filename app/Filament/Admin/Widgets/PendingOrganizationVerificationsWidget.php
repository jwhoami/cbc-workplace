<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingOrganizationVerificationsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('widgets/admin/job-board.pending_verifications.heading');
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    protected function getTableQuery(): Builder
    {
        return Organization::query()
            ->where('verification_state', OrganizationVerificationState::PENDING)
            ->whereNull('suspended_at')
            ->latest('created_at')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('display_name')
                ->label(__('widgets/admin/job-board.columns.display_name'))
                ->url(fn (Organization $record) => OrganizationResource::getUrl('view', ['record' => $record])),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('widgets/admin/job-board.columns.created_at'))
                ->dateTime(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('widgets/admin/job-board.pending_verifications.empty');
    }

    protected function getTableHeaderActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->headerActions([
                Tables\Actions\Action::make('ver-todas')
                    ->label(__('widgets/admin/job-board.pending_verifications.ver_todas'))
                    ->url(fn () => OrganizationResource::getUrl('index', [
                        'tableFilters' => ['verification_state' => ['value' => OrganizationVerificationState::PENDING->value]],
                    ]))
                    ->visible(fn () => Organization::query()
                        ->where('verification_state', OrganizationVerificationState::PENDING)
                        ->whereNull('suspended_at')
                        ->count() > 10),
            ]);
    }
}
