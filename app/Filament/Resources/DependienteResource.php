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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('dependiente1')
                            ->maxValue(50)
                            ->required(),
                        Forms\Components\Radio::make('aplica_covertura1')
                            ->required()
                            ->boolean()
                            ->columns(2),
                        Forms\Components\TextInput::make('estado_migratorio1')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fec_nac1')
                            ->native(false),
                        Forms\Components\TextInput::make('dependiente2')
                            ->maxValue(50),
                        Forms\Components\Radio::make('aplica_covertura2')
                            ->boolean()
                            ->columns(2),
                        Forms\Components\TextInput::make('estado_migratorio2')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fec_nac2')
                            ->native(false),
                        Forms\Components\TextInput::make('dependiente3')
                            ->maxValue(50),
                        Forms\Components\Radio::make('aplica_covertura3')
                            ->boolean()
                            ->columns(2),
                        Forms\Components\TextInput::make('estado_migratorio3')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fec_nac3')
                            ->native(false),
                        Forms\Components\TextInput::make('dependiente4')
                            ->maxValue(50),
                        Forms\Components\Radio::make('aplica_covertura4')
                            ->boolean()
                            ->columns(2),
                        Forms\Components\TextInput::make('estado_migratorio4')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fec_nac4')
                            ->native(false),
                        Forms\Components\TextInput::make('dependiente_x')
                            ->maxValue(50),
                        Forms\Components\Radio::make('aplica_covertura_x')
                            ->boolean()
                            ->columns(2),
                        Forms\Components\TextInput::make('estado_migratorio_x')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fec_nac_x')
                            ->native(false),
                    ])->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('dependiente1')
                    ->searchable(),
                TextColumn::make('aplica_covertura1')
                    ->searchable(),
                TextColumn::make('estado_migratorio1')
                    ->searchable(),
                TextColumn::make('fec_nac1')
                    ->searchable(),
                TextColumn::make('dependiente2')
                    ->searchable(),
                TextColumn::make('aplica_covertura2')
                    ->searchable(),
                TextColumn::make('estado_migratorio2')
                    ->searchable(),
                TextColumn::make('fec_nac2')
                    ->searchable(),
                TextColumn::make('dependiente3')
                    ->searchable(),
                TextColumn::make('aplica_covertura3')
                    ->searchable(),
                TextColumn::make('estado_migratorio3')
                    ->searchable(),
                TextColumn::make('fec_nac3')
                    ->searchable(),
                TextColumn::make('dependiente4')
                    ->searchable(),
                TextColumn::make('aplica_covertura4')
                    ->searchable(),
                TextColumn::make('estado_migratorio4')
                    ->searchable(),
                TextColumn::make('fec_nac4')
                    ->searchable(),
                TextColumn::make('dependiente_x')
                    ->searchable(),
                TextColumn::make('aplica_covertura_x')
                    ->searchable(),
                TextColumn::make('estado_migratorio_x')
                    ->searchable(),
                TextColumn::make('fec_nac_x')
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
