<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Dependiente;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DependienteResource\Pages;
use Filament\Resources\RelationManagers\RelationManager;

class DependienteResource extends Resource
{
    protected static ?string $model = Dependiente::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationGroup = 'Configuración del Sistema';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
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
                        TextInputColumn::make('conyugue_1'),
                        TextInputColumn::make('conyugue_2'),
                        TextInputColumn::make('conyugue_3'),
                    ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('nombre_dependiente')
                    ->searchable(),
                TextColumn::make('aplica_cobertura_dependiente')
                    ->searchable(),
                TextColumn::make('estado_migratorio_dependiente.nombre')
                    ->searchable(),
                TextColumn::make('fec_nac_dependiente')
                    ->searchable(),
                TextColumn::make('cliente.nombre1')
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
           //RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDependientes::route('/'),
            'create' => Pages\CreateDependiente::route('/create'),
            'edit' => Pages\EditDependiente::route('/{record}/edit'),
        ];
    }
}
