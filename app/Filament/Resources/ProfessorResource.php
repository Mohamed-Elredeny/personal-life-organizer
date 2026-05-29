<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessorResource\Pages;
use App\Models\Professor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProfessorResource extends Resource
{
    protected static ?string $model = Professor::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('PhD'); }
    public static function getNavigationLabel(): string { return __('Professors'); }
    public static function getModelLabel(): string { return __('Professor'); }
    public static function getPluralModelLabel(): string { return __('Professors'); }

    public static function getNavigationBadge(): ?string
    {
        $followUps = static::getModel()::whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '<=', today()->addDays(3))
            ->whereNotIn('status', ['closed', 'negative', 'positive'])->count();
        return $followUps > 0 ? (string) $followUps : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Professor'))->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('title')->maxLength(120)
                    ->placeholder('Prof. / Assoc. Prof. / Asst. Prof.'),
                Forms\Components\TextInput::make('university')->maxLength(255),
                Forms\Components\TextInput::make('country')->maxLength(120),
                Forms\Components\TextInput::make('email')->email()->maxLength(255)
                    ->prefixIcon('heroicon-m-envelope'),
                Forms\Components\TextInput::make('lab')->maxLength(255)
                    ->placeholder('Lab name / department'),
                Forms\Components\TextInput::make('research_area')->label(__('Research area'))->maxLength(255)
                    ->placeholder('Medical imaging, CV, NLP...'),
                Forms\Components\TextInput::make('website')->url()->maxLength(1024)
                    ->prefixIcon('heroicon-m-globe-alt'),
            ]),
            Forms\Components\Section::make(__('Outreach status'))->columns(2)->schema([
                Forms\Components\Select::make('status')
                    ->options(self::statusOptions())
                    ->default('planned')->required()->native(false),
                Forms\Components\Select::make('priority')
                    ->options(['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High')])
                    ->default('medium')->required()->native(false),
                Forms\Components\DatePicker::make('last_contact_at')->native(false),
                Forms\Components\DatePicker::make('next_follow_up_at')->native(false),
                Forms\Components\Select::make('scholarship_id')
                    ->relationship('scholarship', 'name')
                    ->label(__('Related scholarship'))
                    ->searchable()->preload(),
                Forms\Components\Select::make('goal_id')
                    ->relationship('goal', 'title')
                    ->label(__('Linked goal'))
                    ->searchable()->preload(),
                Forms\Components\Toggle::make('is_shared')->label(__('Share with partner')),
                Forms\Components\Textarea::make('notes')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn (Professor $r) => trim(($r->title ? $r->title . ' · ' : '') . ($r->university ?? '') . ($r->country ? ' · ' . $r->country : ''), ' ·')),
                Tables\Columns\TextColumn::make('research_area')->label(__('Area'))->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => self::statusColor($state))
                    ->formatStateUsing(fn ($state) => self::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'high' => 'danger', 'medium' => 'warning', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => __(ucfirst($state))),
                Tables\Columns\TextColumn::make('last_contact_at')->label(__('Last contact'))->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('next_follow_up_at')
                    ->label(__('Follow-up'))
                    ->date()->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : ($state && $state->isToday() ? 'warning' : null))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('scholarship.name')->label(__('Scholarship'))->limit(20)->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::statusOptions()),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'),
                ]),
                Tables\Filters\Filter::make('due_followup')
                    ->label(__('Follow-up due'))
                    ->query(fn ($q) => $q->whereDate('next_follow_up_at', '<=', today())
                        ->whereNotIn('status', ['closed', 'negative', 'positive'])),
            ])
            ->actions([
                Tables\Actions\Action::make('email')
                    ->iconButton()
                    ->icon('heroicon-m-envelope')
                    ->url(fn (Professor $r) => $r->email ? 'mailto:' . $r->email : null)
                    ->visible(fn (Professor $r) => filled($r->email))
                    ->tooltip(__('Send email')),
                Tables\Actions\Action::make('open_site')
                    ->iconButton()
                    ->icon('heroicon-m-globe-alt')
                    ->url(fn (Professor $r) => $r->website)
                    ->openUrlInNewTab()
                    ->visible(fn (Professor $r) => filled($r->website))
                    ->tooltip(__('Website')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('next_follow_up_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfessors::route('/'),
            'create' => Pages\CreateProfessor::route('/create'),
            'edit' => Pages\EditProfessor::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'planned'           => __('Planned'),
            'contacted'         => __('Contacted'),
            'replied'           => __('Replied'),
            'meeting_scheduled' => __('Meeting scheduled'),
            'positive'          => __('Positive'),
            'negative'          => __('Negative'),
            'no_response'       => __('No response'),
            'closed'            => __('Closed'),
        ];
    }

    public static function statusColor(string $state): string
    {
        return match ($state) {
            'planned' => 'gray',
            'contacted', 'replied' => 'info',
            'meeting_scheduled' => 'primary',
            'positive' => 'success',
            'negative', 'no_response' => 'danger',
            'closed' => 'gray',
            default => 'gray',
        };
    }
}
