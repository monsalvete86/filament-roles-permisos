<?php

namespace App\Filament\Resources\CondadoResource\Pages;

use App\Filament\Resources\CondadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCondado extends CreateRecord
{
    protected static string $resource = CondadoResource::class;

    protected function   getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Condado created';
    }
}
