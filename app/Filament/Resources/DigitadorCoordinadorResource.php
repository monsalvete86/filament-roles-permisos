<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Models\DigitadorCoordinador;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DigitadorCoordinadorResource\Pages;
use App\Filament\Resources\DigitadorCoordinadorResource\RelationManagers;

class DigitadorCoordinadorResource extends Resource
{
    protected static ?string $model = User::class;

    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $navigationGroup = 'Configuración del Sistema';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

	protected static ?string $navigationLabel = 'Digitadores Coordinadores';

    public static function form(Form $form): Form
    {
        $edit = isset($form->model->exists) ;
        $auxModel = $form->model;
        // dump($auxModel->coordinados[0]->name);
		$auxEdit = $edit ? $form->model->id : '';
		$auxDigitadores = $edit ? DigitadorCoordinador::where('coordinador_id', $auxEdit)->pluck('digitador_id') : [];
		$cont = 0;
		foreach($auxDigitadores as $dig) {
			$aux2[$cont] = $dig;
			$cont++;
		}
		$digitadoresLista = User::query()
			->whereHas('roles', function ($query) {
				$query->where('name', 'digitador');
			})
			->pluck('name', 'id');
        return $form
            ->schema([
                Section::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del Coordinador')
                        ->required()
                        ->disabled(),
                    Select::make('coordinados')
                        ->label('Digitadores Asignados')
                        ->relationship(name: 'coordinados', titleAttribute: 'name')
                        ->options(function () {
                            // Obtener los IDs de los digitadores ya asignados
                            $assignedDigitadorIds = DB::table('digitadores_coordinadores')->pluck('digitador_id');
                            // Obtener los digitadores que no están asignados
                            return User::whereHas('roles', function ($query) {
                                $query->where('name', 'digitador');
                            })
                            ->whereNotIn('id', $assignedDigitadorIds)
                            ->pluck('name', 'id')
                            ->toArray();
                        })
                        ->multiple(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        $resultUsers = User::query();
        // dump($resultUsers);
        return $table
            ->query(
                User::query()->whereHas('roles', function ($query) {
                    $query->where('name', 'coordinador');
                })
            )
            ->columns([
                TextColumn::make('name'),

                TextColumn::make('coordinados.name')
                    ->listWithLineBreaks()
                    ->badge()
                    ->color('gray')
                    ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDigitadorCoordinadors::route('/'),
            'create' => Pages\CreateDigitadorCoordinador::route('/create'),
            'edit' => Pages\EditDigitadorCoordinador::route('/{record}/edit'),
        ];
    }
}
