<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\JobListingState;
use App\Filament\Admin\Resources\JobListingResource;
use App\Models\JobListing;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingJobListingApprovalsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return __('widgets/admin/job-board.pending_offers.heading');
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    protected function getTableQuery(): Builder
    {
        return JobListing::query()
            ->where('state', JobListingState::PENDING)
            ->with('organization:id,display_name')
            ->latest('created_at')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label(__('widgets/admin/job-board.columns.title'))
                ->url(fn (JobListing $record) => JobListingResource::getUrl('view', ['record' => $record])),
            Tables\Columns\TextColumn::make('organization.display_name')
                ->label(__('widgets/admin/job-board.columns.organization')),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('widgets/admin/job-board.columns.created_at'))
                ->dateTime(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->emptyStateHeading(__('widgets/admin/job-board.pending_offers.empty'))
            ->headerActions([
                Tables\Actions\Action::make('ver-todas')
                    ->label(__('widgets/admin/job-board.pending_offers.ver_todas'))
                    ->url(fn () => JobListingResource::getUrl('index', [
                        'tableFilters' => ['state' => ['value' => JobListingState::PENDING->value]],
                    ]))
                    ->visible(fn () => JobListing::query()->where('state', JobListingState::PENDING)->count() > 10),
            ]);
    }
}
