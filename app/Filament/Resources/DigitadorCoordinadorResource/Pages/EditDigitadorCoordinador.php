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
    protected static string $resource = DigitadorCoordinadorResource::class;

    /* protected function mutateFormDataBeforeSave(array $data): array
    {
        dump($data);
        exit; die;
        return $data;
    } */

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
