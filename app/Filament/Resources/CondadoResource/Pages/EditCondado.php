<?php

namespace App\Filament\Resources\CondadoResource\Pages;

use App\Filament\Resources\CondadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCondado extends EditRecord
{
    protected static string $resource = CondadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Condado updated';
    }
}
