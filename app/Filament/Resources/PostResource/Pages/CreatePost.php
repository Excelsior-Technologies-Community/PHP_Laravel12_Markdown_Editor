<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clear auto-save from localStorage after successful save
        // (handled client-side via JS after form submit)
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Post Created')
            ->body('Draft cleared from browser storage automatically.')
            ->success()
            ->send();
    }
}
