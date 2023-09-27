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
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nombre2')
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellido1')
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apellido2')
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefono')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email address')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->email()
                            ->required(),
                        Forms\Components\Radio::make('aplica_cobertura')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->boolean()
                            ->columns(2),
                        Forms\Components\DatePicker::make('fec_nac')
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->native(false),
                        Forms\Components\TextInput::make('direccion')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->maxValue(50),
                        Forms\Components\TextInput::make('codigopostal')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->length(6),
                        Forms\Components\Select::make('estado_id')
                            ->relationship('estado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(! auth()->user()->can('EsAdmin'))
                            ->afterStateUpdated(fn (Set $set) => $set('condado_id', null))
                            ->required(),
                        Forms\Components\Select::make('condado_id')
                            ->options (fn (Get $get): Collection => Condado::all()
                                ->where('estado_id', $get('estado_id'))
                                ->pluck('nombre', 'id')
                            )
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Condado')
                            ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null))
                            ->required(),
                        Forms\Components\Select::make('ciudad_id')
                            ->options (fn (Get $get): Collection => Ciudad::all()
                                ->where('condado_id', $get('condado_id'))
                                ->pluck('nombre', 'id')
                            )
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->searchable()
                            ->preload()
                            ->label('Ciudad')
                            ->required(),
                        Forms\Components\Select::make('estado_migratorio_id')
                            ->relationship('estado_migratorio', 'codigo')
                            ->searchable()
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tipo_trabajo')
                            ->options([
                                '1099' => '1099',
                                'W2' => 'W2',
                            ])
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('personas_aseguradas')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Dependientes' => 'Dependientes',
                                'Conyugue y Dependientes' => 'C&D',
                            ])
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('estado_civil_conyugue')
                            ->options([
                                'Soltero' => 'Soltero',
                                'Casado' => 'Casado',
                                'Cabeza de hogar' => 'Cabeza de hogar',
                                'Opcional' => 'Opcional',
                            ])
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('nombre_conyugue')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->maxLength(255),
                        Forms\Components\Radio::make('aplica_covertura_conyugue')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->boolean()
                            ->required()
                            ->columns(2),
                        Forms\Components\Radio::make('dependientes_fuera_pareja')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->boolean()
                            ->required()
                            ->columns(2),
                        Forms\Components\Select::make('quien_aporta_ingresos')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Juntos' => 'Juntos',
                            ])
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('quien_declara_taxes')
                            ->options([
                                'Solo' => 'Solo',
                                'Conyugue' => 'Conyugue',
                                'Juntos' => 'Juntos',
                            ])
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('total_ingresos_gf')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->label('Total ingresos gf')
                            ->type('number')
                            ->placeholder('Ingrese el total de ingresos GF'),
                        Forms\Components\TextInput::make('estado_cliente')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->required()
                            ->maxValue(50),
                        Forms\Components\TextInput::make('digitador.digitador')
                            ->hidden()
                            ->disabled(! auth()->user()->can('EsAdmin')),
                        Forms\Components\DatePicker::make('fecha_digitadora')
                            ->hidden()
                            ->native(false),
                        Forms\Components\TextInput::make('benefit.benefit')
                            ->hidden()
                            ->disabled(! auth()->user()->can('EsAdmin')),
                        Forms\Components\DatePicker::make('fecha_benefit')
                            ->hidden()
                            ->native(false),
                        Forms\Components\TextInput::make('procesador.procesador')
                            //->hidden()
                            ->disabled(! auth()->user()->can('EsBenefit')),
                        Forms\Components\Select::make('compania_id')
                            ->relationship('compania', 'nombre_companias')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(! auth()->user()->can('EsBenefit')),
                            //->required(),
                        Forms\Components\Select::make('cobertura_ant')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            //->hidden(! auth()->user()->can('EsBenfit'))
                            ->options([
                                'Si' => 'Si',
                                'No' => 'No',
                                'Xinfo' => 'Xinfo',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('codigo_anterior')
                            ->hidden(! auth()->user()->can('EsAdmin'))
                            ->label('Código anterior')
                            ->type('number')
                            ->placeholder('Ingrese el código anterior'),
                        Forms\Components\TextInput::make('ultimo_agente')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('fecha_retiro')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                        Forms\Components\TextInput::make('agente')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->hidden(! auth()->user()->can('Crear roles'))
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('inicio_cobertura')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                            //->required(),
                        Forms\Components\DatePicker::make('fin_cobertura')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                            //->required(),
                        Forms\Components\DatePicker::make('inicio_cobertura_vig')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                            //->required(),
                        Forms\Components\DatePicker::make('fin_cobertura_vig')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                            //->required(),
                        Forms\Components\DatePicker::make('fecha_retiro_cobertura_ant')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->native(false),
                            //->required(),
                        Forms\Components\TextInput::make('image')
                            ->disabled(! auth()->user()->can('EsBenefit'))
                            ->url()
                            ->suffixIcon('heroicon-m-globe-alt'),
                        Forms\Components\Textarea::make('nota_benefit')
                            ->disabled(! auth()->user()->can('EsBenefit')),
                        Forms\Components\Textarea::make('nota_procesador')
                            ->hidden(! auth()->user()->can('EsAdmin')),
                        Forms\Components\Textarea::make('nota_digitadora')
                            ->hidden(! auth()->user()->can('EsAdmin')),
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
                TextColumn::make('digitador.digitador')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fecha_digitadora')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('benefit.benefit')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fecha_benefit')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('procesador.procesador')
                    //->hidden()
                    ->searchable(),
                TextColumn::make('compania.nombre_companias')
                    ->searchable(),
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
                TextColumn::make('inicio_cobertura_vig')
                    ->searchable(),
                TextColumn::make('fin_cobertura_vig')
                    ->searchable(),
                TextColumn::make('fecha_retiro_cobertura_ant')
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
                Tables\Actions\DeleteAction::make()
                    ->hidden(! auth()->user()->can('EsAdmin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
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
