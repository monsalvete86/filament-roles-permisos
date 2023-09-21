<?php

namespace App\Filament\Resources\DependienteResource\Pages;

use App\Filament\Resources\DependienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDependiente extends EditRecord
{
    protected static string $resource = DependienteResource::class;

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
        return 'Dependiente updated';
    }
}
