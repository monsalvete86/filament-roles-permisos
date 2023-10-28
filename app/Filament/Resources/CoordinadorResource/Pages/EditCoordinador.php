<?php

namespace App\Filament\Resources\CoordinadorResource\Pages;

use App\Filament\Resources\CoordinadorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoordinador extends EditRecord
{
    protected static string $resource = CoordinadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
