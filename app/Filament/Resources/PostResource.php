<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\FilamentMarkdownEditor\MarkdownEditor;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Blog Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()->schema([

                    Grid::make(2)->schema([

                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set)
                                => $set('slug', \Str::slug($state))),

                        TextInput::make('slug')
                            ->required(),

                    ]),

                    Grid::make(2)->schema([

                        FileUpload::make('featured_image')
                            ->image()
                            ->directory('posts'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->inline(false),

                    ]),

                ]),

                // Side-by-side: Editor + Live Preview
                Section::make('Content Editor')
                    ->description('Write markdown on the left, see live preview on the right')
                    ->schema([

                        Grid::make(2)->schema([

                            MarkdownEditor::make('content')
                                ->required()
                                ->label('Markdown Editor'),

                            ViewField::make('preview')
                                ->label('Live Preview')
                                ->view('filament.components.markdown-preview')
                                ->viewData([
                                    'postId' => $record?->id ?? 'new',
                                ]),

                        ]),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('featured_image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->is_published ? 'Published' : 'Draft')
                    ->colors([
                        'success' => 'Published',
                        'warning' => 'Draft',
                    ]),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'published') {
                            return $query->where('is_published', true);
                        }
                        if ($data['value'] === 'draft') {
                            return $query->where('is_published', false);
                        }
                        return $query;
                    }),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published Status')
                    ->placeholder('All posts')
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts only'),

            ])

            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_published)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_published' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Post Published')
                            ->body('The post has been published successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->visible(fn ($record) => $record->is_published)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_published' => false]);
                        \Filament\Notifications\Notification::make()
                            ->title('Post Unpublished')
                            ->body('The post has been moved to draft status.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkAction::make('publish')
                    ->label('Publish Selected')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $records->each->update(['is_published' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Posts Published')
                            ->body(count($records) . ' posts have been published.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('unpublish')
                    ->label('Unpublish Selected')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $records->each->update(['is_published' => false]);
                        \Filament\Notifications\Notification::make()
                            ->title('Posts Unpublished')
                            ->body(count($records) . ' posts have been moved to draft status.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\DeleteBulkAction::make(),
            ])

            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}