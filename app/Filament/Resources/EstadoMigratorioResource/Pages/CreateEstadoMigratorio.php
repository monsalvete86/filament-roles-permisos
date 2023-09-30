<?php

namespace App\Filament\Resources\EstadoMigratorioResource\Pages;

use App\Filament\Resources\EstadoMigratorioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoMigratorio extends CreateRecord
{
    protected static string $resource = EstadoMigratorioResource::class;

    protected function   getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'EstadoMigratorio created';
    }
}
