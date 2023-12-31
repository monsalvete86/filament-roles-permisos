<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\EstadoMigratorio;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EstadoMigratorioResource\Pages;
use App\Filament\Resources\EstadoMigratorioResource\RelationManagers;

class EstadoMigratorioResource extends Resource
{
    protected static ?string $model = EstadoMigratorio::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    protected static ?string $navigationGroup = 'Configuración del Sistema';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(),
                        Forms\Components\TextInput::make('codigo')
                            ->required()
                            ->length(6),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('codigo')
                    ->searchable(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstadosMigratorios::route('/'),
            'create' => Pages\CreateEstadoMigratorio::route('/create'),
            'edit' => Pages\EditEstadoMigratorio::route('/{record}/edit'),
        ];
    }
}
