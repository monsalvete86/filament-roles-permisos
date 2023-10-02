<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DependientesRelationManager extends RelationManager
{
    protected static string $relationship = 'dependientes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_dependiente')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Radio::make('aplica_cobertura_dependiente')
                    ->required()
                    ->boolean()
                    ->columns(2),
                Forms\Components\Select::make('estado_migratorio_dependiente_id')
                    ->relationship('estado_migratorio_dependiente', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('fec_nac_dependiente')
                    ->native(false),
                Forms\Components\Select::make('cliente_id')
                    ->relationship('cliente', 'nombre1')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('nombre_dependiente'),
                Tables\Columns\TextColumn::make('aplica_cobertura_dependiente'),
                Tables\Columns\TextColumn::make('estado_migratorio_dependiente.nombre'),
                Tables\Columns\TextColumn::make('fec_nac_dependiente'),
                Tables\Columns\TextColumn::make('cliente.nombre1'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
