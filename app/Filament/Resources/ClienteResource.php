<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Cliente;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClienteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('telefono')
                            ->maxValue(50),
                        Forms\Components\TextInput::make('email')
                            ->label('Email address')
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nombre1')
                            ->maxValue(50)
                            ->required(),
                        Forms\Components\TextInput::make('nombre2')
                            ->maxValue(50)
                            ->required(),
                        Forms\Components\TextInput::make('apellido1')
                            ->maxValue(50)
                            ->required(),
                        Forms\Components\TextInput::make('apellido2')
                            ->maxValue(50)
                            ->required(),
                        Forms\Components\Radio::make('aplica_cobertura')
                            ->required()
                            ->boolean()
                            ->columns(2),
                        Forms\Components\DatePicker::make('fec_nac')
                            ->native(false),
                        Forms\Components\TextInput::make('direccion')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codigopostal')
                            ->length(8),
                        Forms\Components\Select::make('estado_id')
                            ->relationship('estado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('condado_id')
                            ->relationship('condado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('ciudad_id')
                            ->relationship('ciudad', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('estado_migratorio_id')
                            ->relationship('estado_migratorio', 'codigo')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('tipo_trabajo')
                            ->minLength(2)
                            ->maxLength(255),
                        Forms\Components\Radio::make('personas_aseguradas')
                            ->columns(4)
                            ->required()
                            ->options([
                                'Solo' => 'S',
                                'Conyugue' => 'C',
                                'Dependientes' => 'D',
                                'Conyugue y Dependientes' => 'C&D'
                            ])
                            ->descriptions([
                                'Solo' => 'Solo.',
                                'Conyugue' => 'Conyugue.',
                                'Dependientes' => 'Dependientes.',
                                'Conyugue y Dependientes' => 'Conyugue y Dependientes.'
                            ])
                    ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('telefono')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('nombre1')
                    ->searchable(),
                TextColumn::make('nombre2')
                    ->searchable(),
                TextColumn::make('apellido1')
                    ->searchable(),
                TextColumn::make('apellido2')
                    ->searchable(),
                TextColumn::make('aplica_cobertura')
                    ->searchable(),
                TextColumn::make('fec_nac')
                    ->searchable(),
                TextColumn::make('direccion')
                    ->searchable(),
                TextColumn::make('codigopostal')
                    ->searchable(),
                TextColumn::make('estado.nombre')
                    ->searchable(),
                TextColumn::make('condado.nombre')
                    ->searchable(),
                TextColumn::make('ciudad.nombre')
                    ->searchable(),
                TextColumn::make('estado_migratorio.codigo')
                    ->searchable(),
                TextColumn::make('tipo_trabajo')
                    ->searchable(),
                TextColumn::make('personas_aseguradas')
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
