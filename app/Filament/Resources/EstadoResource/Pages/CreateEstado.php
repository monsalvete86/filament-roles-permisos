<?php

namespace App\Filament\Resources\EstadoResource\Pages;

use App\Filament\Resources\EstadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEstado extends CreateRecord
{
    protected static string $resource = EstadoResource::class;

    protected function   getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Estado created';
    }
}
