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
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
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

        $edit = isset($form->model->exists) ;

        $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);

        $disabled = ! auth()->user()->can('editarCliente') && $edit ? true : false;

        $schemas = [
            Section::make('Datos Principales')
                ->schema([
                    TextInput::make('telefono')
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->tel()
                        ->required(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->email()
                        ->required(),
                    TextInput::make('nombre1')
                        ->disabled($disabled)
                        ->required()
                        ->maxLength(255),
                    TextInput::make('nombre2')
                        ->disabled($disabled)
                        ->maxLength(255),
                    TextInput::make('apellido1')
                        ->disabled($disabled)
                        ->required()
                        ->maxLength(255),
                    TextInput::make('apellido2')
                        ->disabled($disabled)
                        ->maxLength(255),
                    Radio::make('aplica_cobertura')
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->required()
                        ->boolean()
                        ->columns(2),
                    DatePicker::make('fec_nac')
                        ->label('Fecha Nacimiento')
                        ->disabled($disabled)
                        ->native(false),
                    TextInput::make('direccion')
                        ->disabled($disabled)
                        ->maxValue(50),
                    Select::make('codigopostal')
                        ->searchable()
                        ->options(fn (Get $get): Collection => $get('estado_id') ?
                            Estado::all()
                                ->where('id', $get('estado_id'))
                                ->sortBy('codigo_postal')
                                ->pluck('codigo_postal', 'codigo_postal') :
                            Estado::all()
                                ->sortBy('codigo_postal')
                                ->pluck('codigo_postal', 'codigo_postal')
                        )
                        ->preload()
                        ->live()
                        ->label('Codigo postal')
                        ->required()
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $set('ciudad_id', null);
                            if ($get('codigopostal'))
                            {
                                $estado = Estado::where('codigo_postal', $get('codigopostal'))->first();
                                if (isset($estado->id)) $set('estado_id', $estado->id);
                            }
                        }),
                    Select::make('estado_id')
                        ->relationship('estado', 'nombre')
                        ->searchable()
                        ->disabled($disabled)
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $set('condado_id', null);
                            if ($get('estado_id') != '')
                            {
                                $estado = Estado::where('id', $get('estado_id'))->first();
                                // dump($codPostEstado->codigo_postal);
                                if (isset($estado->codigo_postal)) $set('codigopostal', $estado->codigo_postal);
                            }
                        })
                        ->required(),
                    Select::make('condado_id')
                        ->options (fn (Get $get): Collection => Condado::all()
                            ->where('estado_id', $get('estado_id'))
                            ->pluck('nombre', 'id')
                        )
                        ->disabled($disabled)
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->label('Condado')
                        ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null))
                        ->required(),
                    Select::make('ciudad_id')
                        ->options (fn (Get $get): Collection => Ciudad::all()
                            ->where('condado_id', $get('condado_id'))
                            ->pluck('nombre', 'id')
                        )
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->searchable()
                        ->preload()
                        ->label('Ciudad')
                        ->required(),
                    Select::make('personas_aseguradas')
                        ->options([
                            'Solo' => 'Solo',
                            'Conyugue' => 'Conyugue',
                            'Dependientes' => 'Dependientes',
                            'Conyugue y Dependientes' => 'C&D',
                        ])
                        ->hidden(auth()->user()->hasRole(['benefit']))
                        ->disabled($disabled)
                        ->live(onBlur: true)
                        ->required()
                        ->native(false),
                ])
                ->collapsible()
                ->columns(4),
            Section::make('Datos a Consultar')
                ->schema(
                    function (Get $get) {
                        $edit = isset($form->model->exists) ;
                        $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);
                        $disabled = ! auth()->user()->can('editarCliente') && $edit ? true : false;

                        if ($get('personas_aseguradas') && $get('personas_aseguradas') != '') {
                            return [
                                Select::make('estado_migratorio_id')
                                    ->relationship('estado_migratorio', 'nombre')
                                    ->searchable()
                                    ->hidden(function (Get $get) {
                                        if (! $get('personas_aseguradas')) { return true; }
                                        if (auth()->user()->hasRole(['benefit'])) return true;
                                        return false;
                                    })
                                    ->disabled($disabled),
                                    //->preload()
                                    //->required(),
                                TextInput::make('documento_migratorio')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->label('Documento Migratorio')
                                    ->type('number')
                                    ->helperText('Si esta en proceso migratorio que documento tiene'),
                                Select::make('tipo_trabajo')
                                    ->options([
                                        '1099' => '1099',
                                        'W2' => 'W2',
                                    ])
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    //->required()
                                    ->default('1099')
                                    ->native(false),
                                Select::make('estado_civil_conyugue')
                                    ->options([
                                        'Soltero' => 'Soltero',
                                        'Casado' => 'Casado',
                                        'Cabeza de hogar' => 'Cabeza de hogar',
                                        'Opcional' => 'Opcional',
                                    ])
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)

                                    ->native(false),
                                TextInput::make('nombre_conyugue')
                                    // ->hidden(!auth()->user()->can('EsDigitador') OR
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->reactive()
                                    ->maxLength(255),
                                Radio::make('aplica_covertura_conyugue')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                Radio::make('dependientes_fuera_pareja')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                Select::make('quien_aporta_ingresos')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                Select::make('quien_declara_taxes')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                TextInput::make('total_ingresos_gf')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled)
                                    ->label('Total ingresos gf')
                                    ->type('number')
                                    ->placeholder('Ingrese el total de ingresos GF'),
                                //Hidden::make('estado_cliente') placeholder
                                Hidden::make('estado_cliente')
                                    ->default('digitado')
                                    ->hidden(! auth()->user()->hasRole('admin')),
                                    // ->required()
                                    // ->maxValue(50),
                                Select::make('digitador.digitador')
                                    ->options([
                                        'Digitado' => 'Digitado',
                                        'Benefit' => 'Benefit',
                                        'Pass' => 'Pass',
                                        'Aceptado' => 'Aceptado',
                                        'Cancelado' => 'Cancelado',
                                        'Retirado' => 'Retirado',
                                    ])
                                    ->native(false)
                                    ->hidden(! auth()->user()->hasRole(['admin'])),
                                DatePicker::make('fecha_digitadora')
                                    ->hidden()
                                    ->native(false),
                                TextInput::make('benefit.benefit')
                                    ->hidden()
                                    ->disabled(! auth()->user()->hasRole(['admin'])),
                                DatePicker::make('fecha_benefit')
                                    ->hidden()
                                    ->native(false),
                                Select::make('compania_id')
                                    ->relationship('compania', 'nombre_companias')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->hidden(!auth()->user()->hasRole(['benefit']))
                                    ->disabled($disabled),
                                    //->required(),
                                TextInput::make('procesador.procesador')
                                    ->hidden(!auth()->user()->hasRole(['admin']))
                                    ->disabled(! auth()->user()->hasRole(['admin'])),
                                Repeater::make('dependientes')
                                    ->relationship()
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                TextInput::make('nombre_dependiente')
                                                    ->required()
                                                    ->maxLength(255),
                                                Radio::make('aplica_cobertura_dependiente')
                                                    ->required()
                                                    ->boolean()
                                                    ->columns(2),
                                                Select::make('estado_migratorio_dependiente_id')
                                                    ->label('Estado Migratorio')
                                                    ->relationship('estado_migratorio_dependiente', 'nombre')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                DatePicker::make('fec_nac_dependiente')
                                                    ->label('Fecha de Nacimiento')
                                                    ->native(false)
                                            ])
                                            ->columns(4),
                                    ])
                                    ->columnSpan(4),
                            ];
                        }

                        return [];
                    }
                )
                ->collapsible()
                ->columns(4)
            ];

        if (function (Get $get) {
                if ($get('personas_aseguradas')) return true;
                 return false;
            }
        )
        {
            function (Get $get) {
                $variable = $get('personas_aseguradas');
                dump($variable);
            };
        }

        if (auth()->user()->hasRole(['benefit', 'admin'])) {
            array_push($schemas, Section::make('Cobertura Anterior')
                ->schema(
                    function (Get $get) {
                        if ( auth()->user()->hasRole(['benefit', 'admin'])) {
                            return [
                                Select::make('cobertura_ant')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    //->hidden(! auth()->user()->can('EsBenfit'))
                                    ->options([
                                        'Si' => 'Si',
                                        'No' => 'No',
                                        'Xinfo' => 'Xinfo',
                                    ])
                                    ->native(false),
                                TextInput::make('codigo_anterior')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'procesador', 'admin']))
                                    ->label('Código anterior')
                                    ->type('number')
                                    ->placeholder('Ingrese el código anterior'),
                                TextInput::make('ultimo_agente')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->maxLength(255),
                                DatePicker::make('fecha_retiro')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->native(false),
                                DatePicker::make('fecha_retiro_cobertura_ant')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->native(false),
                                    //->required(),
                            ];
                        }

                        return [];
                    }
                )
                ->collapsible()
                ->columns(4)
            );
            array_push($schemas, Section::make('Cobertura Vigente')
                ->schema(
                    function (Get $get) {
                        if ( auth()->user()->hasRole(['benefit', 'admin'])) {
                            return [
                                TextInput::make('agente')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->maxLength(255),
                                Radio::make('aplica_covertura_conyugue')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                Radio::make('dependientes_fuera_pareja')
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                Select::make('quien_aporta_ingresos')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->required()
                                    ->native(false),
                                Select::make('quien_declara_taxes')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(auth()->user()->hasRole(['benefit']))
                                    ->required()
                                    ->native(false),
                                    //->required(),
                                DatePicker::make('inicio_cobertura_vig')
                                    ->native(false),
                                    //->required(),
                                DatePicker::make('fin_cobertura_vig')
                                    ->native(false),
                                    //->required(),
                            ];
                        }

                        return [];
                    }
                )
                ->collapsible()
                ->columns(4)
            );
        }

        array_push($schemas, Section::make('Datos Adicionales')
            ->schema([
                TextInput::make('image')
                    ->disabled(! auth()->user()->can('EsBenefit'))
                    ->url()
                    ->suffixIcon('heroicon-m-globe-alt'),
                Forms\Components\Textarea::make('nota_benefit')
                    ->disabled(! auth()->user()->can('EsBenefit')),
                Forms\Components\Textarea::make('nota_procesador')
                    ->hidden(! auth()->user()->can('EsProcesador')),
                Forms\Components\Textarea::make('nota_digitadora')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'procesador', 'admin'])),
            ])
            ->collapsible()
            ->columns(4)
        );

        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema($schemas)->columnSpan(['lg' => 2]),
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
                TextColumn::make('estado_migratorio.nombre')
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
                Tables\Actions\DeleteAction::make(),
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
