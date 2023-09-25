<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Estado;
use App\Models\Cliente;
use App\Models\Condado;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\FileUpload;
use App\Filament\Resources\ClienteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Ciudad;

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
                        Forms\Components\TextInput::make('nombre1')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nombre2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellido1')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellido2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        Forms\Components\Radio::make('aplica_cobertura')
                            ->required()
                            ->boolean()
                            ->columns(2),
                        Forms\Components\DatePicker::make('fec_nac')
                            ->native(false),
                        Forms\Components\TextInput::make('direccion')
                            ->maxValue(50),
                        Forms\Components\TextInput::make('codigopostal')
                            ->required()
                            ->length(6),
                        Forms\Components\Select::make('estado_id')
                            ->relationship('estado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('condado_id', null))
                            ->required(),
                        Forms\Components\Select::make('condado_id')
                            ->options (fn (Get $get): Collection => Condado::all()
                                ->where('estado_id', $get('estado_id'))
                                ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null))
                            ->required(),
                        Forms\Components\Select::make('ciudad_id')
                            ->options (fn (Get $get): Collection => Ciudad::all()
                                ->where('condado_id', $get('condado_id'))
                                ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('estado_migratorio_id')
                            ->relationship('estado_migratorio', 'codigo')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tipo_trabajo')
                            ->options([
                                '1099' => '1099',
                                'W2' => 'W2',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('personas_aseguradas')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Dependientes' => 'Dependientes',
                                'Conyugue y Dependientes' => 'C&D',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('estado_civil_conyugue')
                            ->options([
                                'Soltero' => 'Soltero',
                                'Casado' => 'Casado',
                                'Cabeza de hogar' => 'Cabeza de hogar',
                                'Opcional' => 'Opcional',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('nombre_conyugue')
                            ->maxLength(255),
                        Forms\Components\Radio::make('aplica_covertura_conyugue')
                            ->boolean()
                            ->required()
                            ->columns(2),
                        Forms\Components\Radio::make('dependientes_fuera_pareja')
                            ->boolean()
                            ->required()
                            ->columns(2),
                        Forms\Components\Select::make('quien_aporta_ingresos')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Juntos' => 'Juntos',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('quien_declara_taxes')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Juntos' => 'Juntos',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('total_ingresos_gf')
                            ->label('Total ingresos gf')
                            ->type('number')
                            ->placeholder('Ingrese el total de ingresos GF'),
                        Forms\Components\TextInput::make('estado_cliente')
                            ->required()
                            ->maxValue(50),
                        /* Forms\Components\Select::make('digitadora_id')
                            ->relationship('digitadora', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(), */
                        Forms\Components\DatePicker::make('fecha_digitadora')
                            ->native(false),
                        /* Forms\Components\Select::make('benefit_id')
                            ->relationship('benefit', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(), */
                        Forms\Components\DatePicker::make('fecha_benefit')
                            ->native(false),
                        /* Forms\Components\Select::make('procesador_id')
                            ->relationship('procesador', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(), */
                        Forms\Components\Select::make('cobertura_ant')
                            ->options([
                                'Si' => 'Si',
                                'No' => 'No',
                                'Xinfo' => 'Xinfo',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('codigo_anterior')
                            ->label('Código anterior')
                            ->type('number')
                            ->placeholder('Ingrese el código anterior'),
                        Forms\Components\TextInput::make('ultimo_agente')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fecha_retiro')
                            ->native(false),
                        Forms\Components\TextInput::make('agente')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('inicio_cobertura')
                            ->native(false)
                            ->required(),
                        Forms\Components\DatePicker::make('fin_cobertura')
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('image')
                            ->url()
                            ->suffixIcon('heroicon-m-globe-alt'),
                        Textarea::make('nota_benefit'),
                        Textarea::make('nota_procesador'),
                        Textarea::make('nota_digitadora'),
                    ])->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('nombre1')
                    ->searchable(),
                TextColumn::make('nombre2')
                    ->searchable(),
                TextColumn::make('apellido1')
                    ->searchable(),
                TextColumn::make('apellido2')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->searchable(),
                TextColumn::make('email')
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
                TextColumn::make('estado_civil_conyugue')
                    ->searchable(),
                TextColumn::make('nombre_conyugue')
                    ->searchable(),
                TextColumn::make('aplica_covertura_conyugue')
                    ->searchable(),
                TextColumn::make('dependientes_fuera_pareja')
                    ->searchable(),
                TextColumn::make('quien_aporta_ingresos')
                    ->searchable(),
                TextColumn::make('quien_declara_taxes')
                    ->searchable(),
                TextColumn::make('total_ingresos_gf')
                    ->searchable(),
                TextColumn::make('estado_cliente')
                    ->searchable(),
                /* TextColumn::make('digitadora_id')
                    ->searchable(), */
                TextColumn::make('fecha_digitadora')
                    ->searchable(),
                /* TextColumn::make('benefit_id')
                    ->searchable(), */
                TextColumn::make('fecha_benefit')
                    ->searchable(),
                /* TextColumn::make('procesador_id')
                    ->searchable(),*/
                TextColumn::make('cobertura_ant')
                    ->searchable(),
                TextColumn::make('codigo_anterior')
                    ->searchable(),
                TextColumn::make('ultimo_agente')
                    ->searchable(),
                TextColumn::make('fecha_retiro')
                    ->searchable(),
                TextColumn::make('agente')
                    ->searchable(),
                TextColumn::make('inicio_cobertura')
                    ->searchable(),
                TextColumn::make('fin_cobertura')
                    ->searchable(),
                TextColumn::make('imagen')
                    ->searchable(),
                TextColumn::make('nota_benefit')
                    ->searchable(),
                TextColumn::make('nota_procesador')
                    ->searchable(),
                TextColumn::make('nota_digitadora')
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
            RelationManagers\DependientesRelationManager::class,
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
