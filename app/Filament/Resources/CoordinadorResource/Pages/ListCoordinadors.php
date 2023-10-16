<?php

namespace App\Filament\Resources\CoordinadorResource\Pages;

use App\Filament\Resources\CoordinadorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoordinadors extends ListRecords
{
    protected static string $resource = CoordinadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
