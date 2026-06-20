<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\JobListingResource;
use App\Models\Application;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentApplicationsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('widgets/admin/job-board.recent_applications.heading');
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    protected function getTableQuery(): Builder
    {
        return Application::query()
            ->latest('submitted_at')
            ->with(['member:id,name', 'jobListing:id,title,slug'])
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('member.name')
                ->label(__('widgets/admin/job-board.columns.member')),
            Tables\Columns\TextColumn::make('jobListing.title')
                ->label(__('widgets/admin/job-board.columns.title'))
                ->url(fn (Application $record) => $record->jobListing
                    ? JobListingResource::getUrl('view', ['record' => $record->jobListing])
                    : null),
            Tables\Columns\TextColumn::make('submitted_at')
                ->label(__('widgets/admin/job-board.columns.submitted_at'))
                ->dateTime(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->emptyStateHeading(__('widgets/admin/job-board.recent_applications.empty'));
    }
}
