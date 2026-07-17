<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\PostDraft;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('recoverDraft')
                ->label('Recover Draft')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(function () {
                    return PostDraft::forPost($this->record->id)
                        ->forUser(Auth::id())
                        ->active()
                        ->exists();
                })
                ->action(function () {
                    $draft = PostDraft::forPost($this->record->id)
                        ->forUser(Auth::id())
                        ->active()
                        ->recent()
                        ->first();

                    if ($draft) {
                        $this->form->fill([
                            'title' => $draft->title,
                            'content' => $draft->content,
                            'featured_image' => $draft->featured_image,
                        ]);

                        Notification::make()
                            ->title('Draft Recovered')
                            ->body('Your content has been restored from the last saved draft.')
                            ->success()
                            ->duration(3000)
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Check for backend draft recovery
        $draft = PostDraft::forPost($this->record->id)
            ->forUser(Auth::id())
            ->active()
            ->recent()
            ->first();

        if ($draft && $draft->last_synced_at->gt($this->record->updated_at)) {
            Notification::make()
                ->title('Draft Available')
                ->body('A newer draft is available. Click "Recover Draft" to restore it.')
                ->warning()
                ->duration(5000)
                ->send();
        } else {
            Notification::make()
                ->title('Auto-save Active')
                ->body('Your draft is being auto-saved to browser storage and backend.')
                ->info()
                ->duration(4000)
                ->send();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Save draft to backend after successful save
        PostDraft::updateOrCreate(
            [
                'post_id' => $this->record->id,
                'user_id' => Auth::id(),
            ],
            [
                'title' => $this->record->title,
                'content' => $this->record->content,
                'featured_image' => $this->record->featured_image,
                'metadata' => [
                    'slug' => $this->record->slug,
                    'is_published' => $this->record->is_published,
                ],
                'is_active' => true,
                'last_synced_at' => now(),
            ]
        );
    }
}
