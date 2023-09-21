<?php

namespace App\Filament\Resources\EstadoMigratorioResource\Pages;

use App\Filament\Resources\EstadoMigratorioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstadosMigratorios extends ListRecords
{
    protected static string $resource = EstadoMigratorioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
