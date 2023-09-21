<?php

namespace App\Filament\Resources\DependienteResource\Pages;

use App\Filament\Resources\DependienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDependiente extends CreateRecord
{
    protected static string $resource = DependienteResource::class;

    protected function   getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Dependiente created';
    }
}
