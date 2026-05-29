<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScholarshipResource\Pages;
use App\Filament\Resources\ScholarshipResource\RelationManagers\ProfessorsRelationManager;
use App\Models\Scholarship;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScholarshipResource extends Resource
{
    protected static ?string $model = Scholarship::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('PhD'); }
    public static function getNavigationLabel(): string { return __('Scholarships'); }
    public static function getModelLabel(): string { return __('Scholarship'); }
    public static function getPluralModelLabel(): string { return __('Scholarships'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereIn('status', ['interested', 'shortlisted', 'applied', 'interview'])->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Scholarship'))->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('university')->maxLength(255),
                Forms\Components\TextInput::make('country')->maxLength(120),
                Forms\Components\Select::make('level')
                    ->options([
                        'phd' => __('PhD'),
                        'masters' => __('Masters'),
                        'postdoc' => __('Postdoc'),
                        'other' => __('Other'),
                    ])
                    ->default('phd')->required()->native(false),
                Forms\Components\Select::make('status')
                    ->options(self::statusOptions())
                    ->default('interested')->required()->native(false),
                Forms\Components\DatePicker::make('deadline')->native(false),
                Forms\Components\TextInput::make('funding_type')
                    ->label(__('Funding type'))
                    ->placeholder('Full / Partial / Tuition only')
                    ->maxLength(120),
                Forms\Components\Select::make('goal_id')
                    ->relationship('goal', 'title')
                    ->label(__('Linked goal'))
                    ->searchable()->preload(),
                Forms\Components\Toggle::make('is_shared')->label(__('Share with partner')),
            ]),
            Forms\Components\Section::make(__('Amount & Link'))->columns(3)->schema([
                Forms\Components\Select::make('currency')
                    ->options(Money::shortOptions())
                    ->default('USD')->required()->native(false)->live(),
                Forms\Components\TextInput::make('amount')->numeric()
                    ->prefix(fn ($livewire) => Money::symbol($livewire->data['currency'] ?? 'USD')),
                Forms\Components\TextInput::make('url')->url()->maxLength(1024)
                    ->prefixIcon('heroicon-m-link'),
                Forms\Components\Textarea::make('notes')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->wrap()->weight('bold')
                    ->description(fn (Scholarship $r) => trim(($r->university ?? '') . ' · ' . ($r->country ?? ''), ' ·')),
                Tables\Columns\TextColumn::make('level')->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => self::statusColor($state))
                    ->formatStateUsing(fn ($state) => self::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state, $record) => $state ? Money::format((float) $state, $record->currency, 0) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()->sortable()
                    ->description(function (Scholarship $r) {
                        if (! $r->deadline) return null;
                        $d = $r->daysUntilDeadline();
                        if ($d < 0) return __(':n d overdue', ['n' => abs($d)]);
                        if ($d === 0) return __('Today');
                        return __('in :n d', ['n' => $d]);
                    })
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('professors_count')->counts('professors')
                    ->label(__('Profs'))->badge()->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::statusOptions()),
                Tables\Filters\SelectFilter::make('level')->options([
                    'phd' => 'PhD', 'masters' => 'Masters', 'postdoc' => 'Postdoc', 'other' => 'Other',
                ]),
                Tables\Filters\Filter::make('deadline_30')
                    ->label(__('Due in 30 days'))
                    ->query(fn ($q) => $q->whereBetween('deadline', [today(), today()->addDays(30)])),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->iconButton()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Scholarship $r) => $r->url)
                    ->openUrlInNewTab()
                    ->visible(fn (Scholarship $r) => filled($r->url))
                    ->tooltip(__('Open link')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('deadline');
    }

    public static function getRelations(): array
    {
        return [ProfessorsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScholarships::route('/'),
            'create' => Pages\CreateScholarship::route('/create'),
            'edit' => Pages\EditScholarship::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'interested'  => __('Interested'),
            'shortlisted' => __('Shortlisted'),
            'applied'     => __('Applied'),
            'interview'   => __('Interview'),
            'accepted'    => __('Accepted'),
            'rejected'    => __('Rejected'),
            'withdrawn'   => __('Withdrawn'),
            'enrolled'    => __('Enrolled'),
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'interested', 'shortlisted' => 'gray',
            'applied', 'interview' => 'info',
            'accepted', 'enrolled' => 'success',
            'rejected', 'withdrawn' => 'danger',
            default => 'gray',
        };
    }
}
