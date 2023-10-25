<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\DigitadorCoordinador;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DigitadorCoordinadorResource\Pages;
use App\Filament\Resources\DigitadorCoordinadorResource\RelationManagers;
use Filament\Resources\Forms\Components\BelongsToSelect;

class DigitadorCoordinadorResource extends Resource
{
    protected static ?string $model = DigitadorCoordinador::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Digitadores Coordinadores';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        BelongsToSelect::make('digitador_id')
                            ->relationship('digitador', 'nombre')
                            ->label('Digitador')
                            ->required(),
                        BelongsToSelect::make('coordinador_id')
                            ->relationship('coordinador', 'nombre')
                            ->label('Coordinador')
                            ->required(),
                    ])->columns(2)
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->whereHas('roles', function ($query) {
                    $query->where('nombre', 'coordinador');
                })
            )
            ->listeners([
                'click' => 'openAssignDigitadoresForm',
            ])
            ->columns([
                TextColumn::make('nombre')->label('Nombre'),
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
            'assign' => Pages\AssignDigitadorCoordinador::route('/{record}/assign'),
        ];
    }
}
