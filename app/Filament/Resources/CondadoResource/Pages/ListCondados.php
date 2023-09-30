<?php

namespace App\Filament\Resources\CondadoResource\Pages;

use App\Filament\Resources\CondadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCondados extends ListRecords
{
    protected static string $resource = CondadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
