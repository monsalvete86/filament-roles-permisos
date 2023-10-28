<?php

namespace App\Filament\Resources\DigitadorCoordinadorResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CoordinadorResource;
use App\Filament\Resources\DigitadorCoordinadorResource;

class EditDigitadorCoordinador extends EditRecord
{
    // protected static string $resource = DigitadorCoordinadorResource::class;
    protected static string $resource = CoordinadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
