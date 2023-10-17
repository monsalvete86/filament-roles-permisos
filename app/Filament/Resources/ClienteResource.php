<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Ciudad;
use App\Models\Estado;
use App\Models\Cliente;
use App\Models\Condado;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Compania;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PlanesCompania;
use App\Models\EstadoMigratorio;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Infolists\Components\FileUpload;
use App\Filament\Resources\ClienteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\ClienteResource\RelationManagers;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        $edit = isset($form->model->exists) ;

        $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);

        $disabled = auth()->user()->hasRole(['digitador']) && $edit ? true : false;

        $schemas = [
            Section::make('Datos Principales')
                ->schema([
                    TextInput::make('telefono')
                        ->label('Telefono')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->tel()
                        ->required(),
                    TextInput::make('email')
                        ->label('Correo')
                        ->placeholder('Direccion de correo electronico')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->email()
                        ->required(),
                    TextInput::make('nombre1')
                        ->label('Nombre 1')
                        ->placeholder('Nombre del asegurado principal')
                        ->disabled($disabled)
                        ->required()
                        ->maxLength(255),
                    TextInput::make('nombre2')
                        ->label('Nombre2')
                        ->disabled($disabled)
                        ->maxLength(255),
                    TextInput::make('apellido1')
                        ->disabled($disabled)
                        ->required()
                        ->placeholder('Apellido del asegurado principal')
                        ->maxLength(255),
                    TextInput::make('apellido2')
                        ->disabled($disabled)
                        ->maxLength(255),
                    Radio::make('aplica_cobertura')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->required()
                        ->boolean()
                        ->helperText('Aplica para cobertura?')
                        ->columns(2),
                    DatePicker::make('fec_nac')
                        ->label('Fecha Nacimiento')
                        ->required()
                        ->disabled($disabled)
                        ->placeholder('Ingrese la fecha de nacimiento'),
                        // ->native(false),
                    TextInput::make('direccion')
                        ->disabled($disabled)
                        ->helperText('Incluya la dirección lo más clara posible, no ingrese ciudad ni estado en este campo')
                        ->maxValue(50),
                    Select::make('codigopostal')
                        ->searchable()
                        ->helperText('Ingrese el codigo postal')
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->searchable()
                        ->preload()
                        ->label('Ciudad')
                        ->required(),
                    Select::make('tipo_trabajo')
                        ->label('Tipo de trabajo')
                        ->helperText('Tipo de trabajo W2 o 1099')
                        ->options([
                            '1099' => '1099',
                            'W2' => 'W2',
                        ])
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->default('1099')
                        ->native(false),
                    Select::make('estado_migratorio_id')
                        ->label('Estado Migratorio')
                        ->relationship('estado_migratorio', 'nombre')
                        ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->preload()
                        ->disabled($disabled),
                    TextInput::make('documento_migratorio')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->disabled($disabled)
                        ->label('Documento Migratorio')
                        ->type('number')
                        ->helperText('Si esta en proceso migratorio que documento tiene'),
                    Select::make('personas_aseguradas')
                        ->options([
                            'Solo' => 'Solo',
                            'Conyugue' => 'Conyugue',
                            'Dependientes' => 'Dependientes',
                            'Conyugue y Dependientes' => 'Conyugue y Dependientes',
                        ])
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                        ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                        ->disabled($disabled)
                        ->live(onBlur: true)
                        ->required()
                        ->native(false),
                        Hidden::make('benefit_id')
                        ->hidden(! auth()->user()->hasRole(['benefit']))
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['benefit'])) {
                                $set('benefit_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    Hidden::make('digitador_id')
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['digitador'])) {
                                $set('digitador_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    Hidden::make('fecha_digitadora')
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['digitador'])) {
                                $set('fecha_digitadora', now());
                                return now();
                            }
                            return '';
                        }),
                    Hidden::make('procesador_id')
                        ->hidden(! auth()->user()->hasRole(['procesador']))
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['procesador'])) {
                                $set('procesador_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    Hidden::make('fecha_procesador')
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['procesador'])) {
                                $set('fecha_procesador', now());
                                return now();
                            }
                            return '';
                        }),
                    Hidden::make('benefit_id')
                        ->hidden(! auth()->user()->hasRole(['benefit']))
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['benefit'])) {
                                $set('benefit_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    Hidden::make('fecha_benefit')
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['benefit'])) {
                                $set('fecha_benefit', now());
                                return now();
                            }
                            return '';
                        }),
                    Hidden::make('admin_id')
                        ->hidden(! auth()->user()->hasRole(['admin']))
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['admin'])) {
                                $set('admin_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    Hidden::make('fecha_admin')
                        ->default(function (Set $set) {
                            if (auth()->user()->hasRole(['admin'])) {
                                $set('fecha_admin', now());
                                return now();
                            }
                            return '';
                        }),
                ])
                ->collapsible()
                ->columns(4),
            !auth()->user()->hasRole(['benefit']) ?
                Section::make('Datos a Consultar')
                    ->schema(
                        function (Get $get) use ($form) {

                            $edit = isset($form->model->exists) ;
                            $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);
                            $disabled = auth()->user()->hasRole(['digitador']) && $edit ? true : false;
                            $auxSchema = [];

                            array_push($auxSchema, Section::make('Datos a afiliacion')
                                ->schema([
                                    Select::make('quien_aporta_ingresos')
                                        ->helperText('Quíen aporta los ingresos del hogar?')
                                        ->options([
                                            'Solo' => 'Solo',
                                            'Conyugue' => 'Conyugue',
                                            'Juntos' => 'Juntos',
                                        ])
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->required(),
                                    Select::make('quien_declara_taxes')
                                        ->helperText('Como declara los impuestos (Taxes)?')
                                        ->options([
                                            'Solo' => 'Solo',
                                            'Conyugue' => 'Conyugue',
                                            'Juntos' => 'Juntos',
                                        ])
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->required(),
                                    TextInput::make('total_ingresos_gf')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->label('Total ingresos gf')
                                        ->type('number')
                                        ->placeholder('Total ingresos del grupo familiar'),
                                    Select::make('compania_id')
                                        ->searchable()
                                        ->options (fn (Get $get): Collection => Compania::all()
                                            ->where('estado_id', $get('estado_id'))
                                            ->pluck('nombre_companias', 'id')
                                        )
                                        ->helperText('Compañia elegida para dar la cobertura')
                                        ->label('Compania aseguradora')
                                        ->disabled($disabled)
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->preload()
                                        ->live()
                                        ->required(),
                                    Select::make('plan_compania_aseguradora')
                                        ->searchable()
                                        ->options (fn (Get $get): Collection => PlanesCompania::all()
                                            ->where('compania_id', $get('compania_id'))
                                            ->pluck('nombre', 'id')
                                        )
                                        ->helperText('Plan elegido para dar la cobertura')
                                        ->label('Plan seleccionado por el cliente')
                                        ->disabled($disabled)
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->preload()
                                        ->live()
                                        ->required(),
                                    /*TextInput::make('plan_compania_aseguradora')
                                        ->placeholder('Plan seleccionado por el cliente')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->required(),*/
                                    TextInput::make('prima_mensual')
                                        ->label('Prima mensual')
                                        ->placeholder('Prima mensual')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->type('number'),
                                    TextInput::make('deducible')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('maximo_bolsillo')
                                        ->label('Maximo bolsillo')
                                        ->placeholder('Máximo de bolsillo')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('medicamento_generico')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('medico_primario')
                                        ->label('Medico primario')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('medico_especialista')
                                        ->label('Medico especialista')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('sala_emergencia')
                                        ->placeholder('Sala de emergencia')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    TextInput::make('subsidio')
                                        ->label('Subsidio')
                                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                        ->disabled($disabled)
                                        ->maxLength(255),
                                    DatePicker::make('inicio_cobertura')
                                        ->label('Fecha inicio cobertura')
                                        ->hidden(! auth()->user()->hasRole(['digitador' , 'admin' , 'procesador']))
                                        ->required()
                                        ->disabled($disabled),
                                    DatePicker::make('fin_cobertura')
                                        ->label('Fecha fin cobertura')
                                        ->after('inicio_cobertura')
                                        ->hidden(! auth()->user()->hasRole(['digitador' , 'admin' , 'procesador']))
                                        ->required()
                                        ->disabled($disabled),
                                    DatePicker::make('fecha_retiro')
                                        ->label('Fecha retiro')
                                        ->after('inicio_cobertura')
                                        ->hidden(! auth()->user()->hasRole(['digitador' , 'admin' , 'procesador']))
                                        ->disabled($disabled),
                                ])
                                ->collapsible()
                                ->columns(4)
                            );

                            if ($get('personas_aseguradas') && ($get('personas_aseguradas') === 'Conyugue' || $get('personas_aseguradas') === 'Conyugue y Dependientes')) {
                                array_push($auxSchema, Section::make('Datos Conyugue')
                                    ->schema([
                                        TextInput::make('nombre_conyugue')
                                            ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                            ->disabled($disabled)
                                            ->required()
                                            ->maxLength(255),
                                        Radio::make('aplica_covertura_conyugue')
                                            ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                            ->disabled($disabled)
                                            ->boolean()
                                            ->required()
                                            ->columns(2),
                                        Select::make('estado_migratorio_conyugue_id')
                                            ->label('Estado migratorio conyugue')
                                            ->relationship('estado_migratorio', 'nombre')
                                            ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                            ->helperText('Elija entre las siguientes opciones')
                                            ->preload()
                                            ->disabled($disabled),
                                        DatePicker::make('fec_nac_conyugue')
                                            ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                                            ->label('Fecha nacimiento conyugue ')
                                            ->required()
                                            ->disabled($disabled)
                                            ->helperText('Ingrese la fecha de nacimiento del conyugue'),
                                    ])
                                    ->collapsible()
                                    ->columns(4)
                                );
                            }

                            if ($get('personas_aseguradas') && ($get('personas_aseguradas') === 'Dependientes' || $get('personas_aseguradas') === 'Conyugue y Dependientes')) {
                                array_push($auxSchema, Section::make('Datos Dependientes')
                                    ->schema([
                                        Repeater::make('dependientes')
                                            ->relationship()
                                            ->label('Dependientes')
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
                                                        ->label('Estado migratorio dependiente')
                                                        ->relationship('estado_migratorio_dependiente', 'nombre')
                                                        ->searchable()
                                                        ->preload()
                                                        ->required(),
                                                    DatePicker::make('fec_nac_dependiente')
                                                        ->label('Fecha de Nacimiento')
                                                        ->required(),
                                                ])
                                                ->columns(4),
                                            ])
                                            ->columnSpan(4),
                                    ])
                                    ->collapsible()
                                    ->columns(4)
                                );
                            }

                            return $auxSchema;
                        }
                    )
                    ->collapsible()
                    ->columns(4) :
                Section::make('')
                    ->schema([])
        ];


        if (auth()->user()->hasRole(['benefit', 'admin'])) {
            array_push($schemas, Section::make('Cobertura Anterior')
                ->schema(
                    function (Get $get) {
                        if ( auth()->user()->hasRole(['benefit', 'admin'])) {
                            return [
                                Select::make('cobertura_ant')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
                                    ->label('Cobertura Anterior')
                                    ->native(false)
                                    ->options([
                                        'Si' => 'Si',
                                        'No' => 'No',
                                        'Xinfo' => 'Xinfo',
                                    ]),
                                TextInput::make('ultimo_agente')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
                                    ->maxLength(255),
                                DatePicker::make('fecha_inicio_cobertura_ant')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin'])),
                                DatePicker::make('fecha_retiro_cobertura_ant')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
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
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
                                    ->maxLength(255),
                                DatePicker::make('inicio_cobertura_vig')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
                                    ->native(false),
                                DatePicker::make('fin_cobertura_vig')
                                    ->disabled(! auth()->user()->hasRole(['benefit', 'admin']))
                                    ->native(false),
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
                TextInput::make('audio')
                    ->label('Audio')
                    ->hidden(! auth()->user()->hasRole(['coordinador', 'admin' , 'procesador']))
                    ->disabled(! auth()->user()->hasRole(['coordinador', 'admin']))
                    ->url()
                    ->suffixIcon('heroicon-m-globe-alt'),
                TextInput::make('image')
                    ->label('Imagen')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin' , 'procesador']))
                    ->url()
                    ->suffixIcon('heroicon-m-globe-alt'),
                Textarea::make('nota_benefit')
                    ->label('Nota benefit')
                    ->placeholder('Nota benefit')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->disabled(! auth()->user()->can('EsBenefit'))
                    ->columnSpan(3),
                Textarea::make('nota_procesador')
                    ->label('Nota procesador(a)')
                    ->placeholder('Nota del procesador')
                    ->hidden(! auth()->user()->hasRole(['procesador', 'admin']))
                    ->disabled(! auth()->user()->can('EsProcesador'))
                    ->columnSpan(3),
                Textarea::make('nota_digitadora')
                    ->label('Nota digitador(a)')
                    ->placeholder('Nota del digitador')
                    ->hidden(! auth()->user()->hasRole(['digitador' , 'admin']))
                    ->disabled(! auth()->user()->can('EsDigitador'))
                    ->columnSpan(3),
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

    public static function verificarPerfil(Builder $query): Builder {
        if (auth()->user()->hasRole(['benefit'])) {
            return $query->where('benefit_id', '=', auth()->user()->id);    
        }

        if (auth()->user()->hasRole(['procesador'])) {
            return $query->where('procesador_id', '=', auth()->user()->id);    
        }

        if (auth()->user()->hasRole(['digitador'])) {
            return $query->where('digitador_id', '=', auth()->user()->id);    
        }

        return $query->where('id', '>', '0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('created_at')
                    ->label('Fecha Digitado')
                    ->sortable()
                    ->searchable()
                    ->date(),
                TextColumn::make('telefono')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                    ->searchable(),
                TextColumn::make('nombre1')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('nombre2')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('apellido1')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('apellido2')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('aplica_cobertura')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state ? 'Si' : 'No'),
                TextColumn::make('fec_nac')
                    ->label('Fecha nacimiento')
                    ->date()
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                    ->searchable(),
                TextColumn::make('direccion')
                    ->searchable(),
                TextColumn::make('codigopostal')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('estado.nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('condado.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('ciudad.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('compania.nombre_companias')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('personas_aseguradas')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                    ->searchable(),
                TextColumn::make('nombre1_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('nombre2_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('apellido1_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('apellido2_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('aplica_covertura_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fec_nac_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('dependientes.nombre1_dependiente')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin' , 'procesador']))
                    ->listWithLineBreaks()
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('estado_migratorio.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('documento_migratorio')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('tipo_trabajo')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('quien_aporta_ingresos')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('quien_declara_taxes')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('total_ingresos_gf')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('prima_mensual')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('deducible')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('maximo_bolsillo')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('medicamento_generico')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('medico_primario')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('medico_especialista')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('sala_emergencia')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('subsidio')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('cobertura_ant')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('fecha_inicio_cobertura_ant')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('ultimo_agente')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('fecha_retiro_cobertura_ant')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('agente')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('inicio_cobertura_vig')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('fin_cobertura_vig')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('imagen')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('nota_benefit')
                    ->hidden(! auth()->user()->hasRole(['benefit', 'admin']))
                    ->searchable(),
                TextColumn::make('nota_procesador')
                    ->hidden(! auth()->user()->hasRole(['procesador', 'admin']))
                    ->searchable(),
                TextColumn::make('nota_digitadora')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('estado_cliente')
                    // ->hidden()
                    ->searchable(isIndividual: true),
                TextColumn::make('digitador.name')
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador']))
                    ->searchable(isIndividual: true),
                TextColumn::make('fecha_digitadora')
                    ->date()
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador']))
                    ->searchable(),
                TextInputColumn::make('audio')
                    ->extraAttributes(['class' => 'w-100 min-w-full'])
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador', 'procesador']))
                    ->disabled(! auth()->user()->hasRole(['admin', 'coordinador'])),
                SelectColumn::make('benefit_id')
                    ->extraAttributes(['class' => 'w-100 min-w-full'])
                    ->label('Benefit Asignado')
                    ->disabled(fn ($record): bool => $record->fecha_benefit ? true : false)
                    ->options (fn (Get $get): Collection => User::all()
                        //->where('id', $get('estado_id'))
                        ->pluck('name', 'id')
                    ),
                TextColumn::make('fecha_benefit')
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador']))
                    ->date()
                    ->searchable(),
                SelectColumn::make('procesador_id')
                    //->extraAttributes(['class' => 'w-100 min-w-full'])
                    ->label('Procesador Asignado')
                    ->searchable()
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador']))
                    ->disabled(fn ($record): bool => $record->fecha_benefit && $record->audio ? false : true)
                    ->options (fn (Get $get): Collection => User::all()
                        //->where('id', $get('estado_id'))
                        ->pluck('name', 'id')
                    ),
                TextColumn::make('fecha_procesador')
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador']))
                    ->searchable(),
                TextInputColumn::make('crm_id')
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador', 'procesador']))
                    ->disabled(! auth()->user()->hasRole(['admin', 'coordinador'])),
                TextInputColumn::make('member_id')
                    ->hidden(! auth()->user()->hasRole(['admin', 'coordinador', 'procesador']))
                    ->disabled(! auth()->user()->hasRole(['admin', 'coordinador'])),
                /* TextColumn::make('admin.name')
                    ->hidden(! auth()->user()->hasRole(['admin']))
                    ->searchable(), */
                
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->modifyQueryUsing(fn (Builder $query) => self::verificarPerfil($query))
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                /* Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),*/
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\DependientesRelationManager::class,
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
