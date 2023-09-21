<?php

namespace App\Filament\Resources\DependienteResource\Pages;

use App\Filament\Resources\DependienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDependientes extends ListRecords
{
    protected static string $resource = DependienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
