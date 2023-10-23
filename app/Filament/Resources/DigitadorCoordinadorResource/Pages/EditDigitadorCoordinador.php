<?php

namespace App\Filament\Resources\DigitadorCoordinadorResource\Pages;

use App\Filament\Resources\DigitadorCoordinadorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitadorCoordinador extends EditRecord
{
    protected static string $resource = DigitadorCoordinadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
