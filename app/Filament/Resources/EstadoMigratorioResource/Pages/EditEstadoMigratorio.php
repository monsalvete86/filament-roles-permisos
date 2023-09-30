<?php

namespace App\Filament\Resources\EstadoMigratorioResource\Pages;

use App\Filament\Resources\EstadoMigratorioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstadoMigratorio extends EditRecord
{
    protected static string $resource = EstadoMigratorioResource::class;

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
        return 'EstadoMigratorio updated';
    }
}
