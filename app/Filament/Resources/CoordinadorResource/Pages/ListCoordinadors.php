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
        // $usuario = auth()->user()->hasRole('admin');
        // dump($usuario);
        if (auth()->user()->can('EsAdmin')){
            return [
                Actions\CreateAction::make(),
            ];
        } else { return []; }
    }
}
