<?php

namespace App\Filament\Resources\CompaniaResource\Pages;

use App\Filament\Resources\CompaniaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanias extends ListRecords
{
    protected static string $resource = CompaniaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
