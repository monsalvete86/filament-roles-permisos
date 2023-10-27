<?php

namespace App\Filament\Resources\DigitadorCoordinadorResource\Pages;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Resources\Pages\Page;
use App\Models\DigitadorCoordinador;
use Filament\Forms\Form;
use App\Filament\Resources\DigitadorCoordinadorResource;

class AssignDigitadoresPage extends Page
{
    protected static string $view = 'filament.pages.assign-digitadores-page';

    public $coordinador;

    protected static string $resource = DigitadorCoordinadorResource::class;

    public function mount($coordinadorId)
    {
        $this->coordinador = User::findOrFail($coordinadorId);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('digitadores')
                        ->label('Selecciona los digitadores')
                        ->options(
                            User::whereHas('roles', function ($query) {
                                $query->where('name', 'digitador');
                            })->pluck('id', 'name')->toArray()
                        )
                        ->stacked()
            ])
            ->submit('Guardar')
            ->redirectAfterSubmit('/admin/digitadores-coordinador');
    }

    public function save()
    {
        foreach ($this->data['digitadores'] as $digitadorId) {
            DigitadorCoordinador::updateOrCreate(
                ['coordinador_id' => $this->coordinador->id, 'digitador_id' => $digitadorId]
            );
        }
    }
}
