<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Estado;
use App\Models\Cliente;
use App\Models\Condado;
use App\Models\Compania;
use App\Models\EstadoMigratorio;
use App\Models\User;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['digitador_id'] = auth()->user()->id;

        return $data;
    }

    public static function form(Form $form): Form
    {

        $edit = isset($form->model->exists) ;

        $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);

        $disabled = ! auth()->user()->can('editarCliente') && $edit ? true : false;

        $schemas = [
            Section::make('Datos Principales')
                ->schema([
                    TextInput::make('telefono')
                        ->label('Telefono')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                        ->disabled($disabled)
                        ->tel()
                        ->required(),
                    TextInput::make('email')
                        ->label('Correo')
                        ->placeholder('Direccion de correo electronico')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                        ->disabled($disabled)
                        ->required()
                        ->boolean()
                        ->helperText('Aplica para cobertura?')
                        ->columns(2),
                    DatePicker::make('fec_nac')
                        ->label('Fecha Nacimiento')
                        ->required()
                        ->disabled($disabled)
                        ->placeholder('Ingrese la fecha de nacimiento')
                        ->native(false),
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
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
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                        ->disabled($disabled)
                        ->searchable()
                        ->preload()
                        ->label('Ciudad')
                        ->required(),
                    Select::make('estado_migratorio_id')
                        ->label('Estado Migratorio')
                        ->relationship('estado_migratorio', 'nombre')
                        ->searchable()
                        ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                        ->preload()
                        ->disabled($disabled),
                    Select::make('personas_aseguradas')
                        ->options([
                            'Solo' => 'Solo',
                            'Conyugue' => 'Conyugue',
                            'Dependientes' => 'Dependientes',
                            'Conyugue y Dependientes' => 'Conyugue y Dependientes',
                        ])
                        ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                        ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                        ->disabled($disabled)
                        ->live(onBlur: true)
                        ->required()
                        ->native(false),
                    TextInput::make('benefit_id')
                        ->type('text')
                        ->hidden(! auth()->user()->hasRole(['benefit']))
                        ->default(function (Set $set) {
                            //dump(auth()->user()->id);
                            if (auth()->user()->hasRole(['benefit'])) {
                                $set('benefit_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    TextInput::make('digitador_id')
                        ->type('text')
                        ->hidden(! auth()->user()->hasRole(['digitador']))
                        ->default(function (Set $set) {
                            //dump(auth()->user()->id);
                            if (auth()->user()->hasRole(['digitador'])) {
                                $set('digitador_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    TextInput::make('procesador_id')
                        ->type('text')
                        ->hidden(! auth()->user()->hasRole(['procesador']))
                        ->default(function (Set $set) {
                            //dump(auth()->user()->id);
                            if (auth()->user()->hasRole(['procesador'])) {
                                $set('procesador_id', auth()->user()->id);
                                return auth()->user()->id;
                            }
                            return '';
                        }),
                    /*TextInput::make('benefit2')
                        // ->hidden(! auth()->user()->hasRole(['admin']))
                        ->default(function (Set $set) {
                            dump(auth()->user()->name);
                            if (auth()->user()->hasRole(['admin'])) {
                                $set('benefit2', auth()->user()->name);
                                return auth()->user()->name;
                            }
                            return '';
                        }),*/
                ])
                ->collapsible()
                ->columns(4),
            Section::make('Datos a Consultar')
                ->schema(
                    function (Get $get) {
                        $edit = isset($form->model->exists) ;
                        $disabled = ! auth()->user()->hasRole(['digitador', 'procesador', 'admin']);
                        $disabled = ! auth()->user()->can('editarCliente') && $edit ? true : false;
                        /*if ($get('personas_aseguradas') && $get('personas_aseguradas') !== '') {
                            return [
                                Select::make('estado_migratorio_id')
                                    ->relationship('estado_migratorio', 'nombre')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->searchable()
                                    ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                                    ->hidden(function (Get $get) {
                                        if (! $get('personas_aseguradas')) { return true; }
                                        if (auth()->user()->hasRole(['digitador', 'admin'])) return true;
                                        return false;
                                    })
                                    ->disabled($disabled),
                                    //->preload()
                                    //->required(),
                                Select::make('estado_civil_conyugue')
                                    ->options([
                                        'Soltero' => 'Soltero',
                                        'Casado' => 'Casado',
                                        'Cabeza de hogar' => 'Cabeza de hogar',
                                        'Opcional' => 'Opcional',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->native(false),
                                TextInput::make('codigo_anterior')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'procesador', 'admin']))
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->label('Código anterior')
                                    ->type('number')
                                    ->placeholder('Ingrese el código anterior'),
                                DatePicker::make('fecha_retiro_cobertura_ant')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->native(false),
                                Hidden::make('estado_cliente')
                                    ->default('digitado')
                                    ->hidden(! auth()->user()->hasRole('admin')),
                                DatePicker::make('fecha_digitadora')
                                    ->hidden()
                                    ->native(false),
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
                                Select::make('estado_civil_conyugue')
                                    ->options([
                                        'Soltero' => 'Soltero',
                                        'Casado' => 'Casado',
                                        'Cabeza de hogar' => 'Cabeza de hogar',
                                        'Opcional' => 'Opcional',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->native(false),
                                Select::make('digitador')
                                    ->options(fn (Get $get): Collection => User::all()
                                        ->where('id', auth()->user()->id)
                                        ->pluck('name' , 'digitador')
                                    )
                                    ->disabled($disabled)
                                    ->selectablePlaceholder(false)
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin'])),
                                Select::make('procesador')
                                    ->options(fn (Get $get): Collection => User::all()
                                        ->where('id', auth()->user()->id)
                                        ->pluck('name' , 'procesador')
                                    )
                                    ->disabled($disabled)
                                    ->selectablePlaceholder(false)
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->hidden(! auth()->user()->hasRole(['procesador'])),
                                Select::make('benefit')
                                    ->options(fn (Get $get): Collection => User::all()
                                        ->where('id', auth()->user()->id)
                                        ->pluck('name' , 'benefit')
                                    )
                                    ->disabled($disabled)
                                    ->selectablePlaceholder(false)
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->hidden(! auth()->user()->hasRole(['benefit'])),
                            ];
                        }*/
                        if ($get('personas_aseguradas') && $get('personas_aseguradas') === 'Solo') {
                            return [
                                Select::make('estado_migratorio_id')
                                    ->label('Estado Migratorio')
                                    ->relationship('estado_migratorio', 'nombre')
                                    ->searchable()
                                    ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->preload()
                                    ->disabled($disabled),
                                TextInput::make('documento_migratorio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Documento Migratorio')
                                    ->type('number')
                                    ->helperText('Si esta en proceso migratorio que documento tiene'),
                                Select::make('tipo_trabajo')
                                    ->label('Tipo de trabajo')
                                    ->helperText('Tipo de trabajo W2 o 1099')
                                    ->options([
                                        '1099' => '1099',
                                        'W2' => 'W2',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->default('1099')
                                    ->native(false),
                                Select::make('quien_aporta_ingresos')
                                    ->helperText('Quíen aporta los ingresos del hogar?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                Select::make('quien_declara_taxes')
                                    ->helperText('Como declara los impuestos (Taxes)?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                TextInput::make('total_ingresos_gf')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Total ingresos gf')
                                    ->type('number')
                                    ->placeholder('Total ingresos del grupo familiar'),
                                Select::make('compania_aseguradora')
                                    ->searchable()
                                    ->helperText('Compañia elegida para dar la cobertura')
                                    ->options(fn (Get $get): Collection => $get('compania_id') ?
                                        Compania::all()
                                            ->where('id', $get('compania_id'))
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias') :
                                        Compania::all()
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias')
                                    )
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled),
                                TextInput::make('plan_compania_aseguradora')
                                    ->placeholder('Plan seleccionado por el cliente')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('prima_mensual')
                                    ->label('Prima mensual')
                                    ->placeholder('Prima mensual')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->type('number'),
                                TextInput::make('deducible')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('maximo_bolsillo')
                                    ->label('Maximo bolsillo')
                                    ->placeholder('Máximo de bolsillo')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medicamento_generico')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_primario')
                                    ->label('Medico primario')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_especialista')
                                    ->label('Medico especialista')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('sala_emergencia')
                                    ->placeholder('Sala de emergencia')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('subsidio')
                                    ->label('Subsidio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                            ];
                        }

                        if ($get('personas_aseguradas') && ($get('personas_aseguradas') === 'Conyugue' || $get('personas_aseguradas') === 'Conyugue y Dependientes')) {
                            return [
                                TextInput::make('nombre_conyugue')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->reactive()
                                    ->maxLength(255),
                                Radio::make('aplica_covertura_conyugue')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                Select::make('estado_migratorio_conyugue')
                                    ->searchable()
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->helperText('Elija entre las siguientes opciones')
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->disabled($disabled),
                                DatePicker::make('fec_nac_conyugue')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->label('Fecha nacimiento pareja ')
                                    ->required()
                                    ->disabled($disabled)
                                    ->helperText('Ingrese la fecha de nacimiento de la pareja')
                                    ->native(false),
                                Repeater::make('dependientes')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
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
                                Radio::make('dependientes_fuera_pareja')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->boolean()
                                    ->required()
                                    ->columns(2),
                                TextInput::make('documento_migratorio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Documento Migratorio')
                                    ->type('number')
                                    ->helperText('Si esta en proceso migratorio que documento tiene'),
                                Select::make('tipo_trabajo')
                                    ->helperText('Tipo de trabajo W2 o 1099')
                                    ->options([
                                        '1099' => '1099',
                                        'W2' => 'W2',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->default('1099')
                                    ->native(false),
                                Select::make('quien_aporta_ingresos')
                                    ->helperText('Quíen aporta los ingresos del hogar?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                Select::make('quien_declara_taxes')
                                    ->helperText('Como declara los impuestos (Taxes)?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                TextInput::make('total_ingresos_gf')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Total ingresos gf')
                                    ->type('number')
                                    ->placeholder('Total ingresos del grupo familiar'),
                                Select::make('compania_aseguradora')
                                    ->searchable()
                                    ->helperText('Compañia elegida para dar la cobertura')
                                    ->options(fn (Get $get): Collection => $get('compania_id') ?
                                        Compania::all()
                                            ->where('id', $get('compania_id'))
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias') :
                                        Compania::all()
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias')
                                    )
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled),
                                TextInput::make('plan_compania_aseguradora')
                                    ->placeholder('Plan seleccionado por el cliente')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('prima_mensual')
                                    ->label('Prima mensual')
                                    ->placeholder('Prima mensual')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->type('number'),
                                TextInput::make('deducible')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('maximo_bolsillo')
                                    ->label('Maximo bolsillo')
                                    ->placeholder('Máximo de bolsillo')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medicamento_generico')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_primario')
                                    ->label('Medico primario')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_especialista')
                                    ->label('Medico especialista')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('sala_emergencia')
                                    ->placeholder('Sala de emergencia')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('subsidio')
                                    ->label('Subsidio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                            ];
                        }

                        if ($get('personas_aseguradas') && ($get('personas_aseguradas') === 'Dependientes' || $get('personas_aseguradas') === 'Conyugue y Dependientes')) {
                            return [
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
                                Select::make('estado_migratorio_id')
                                    ->label('Estado Migratorio')
                                    ->relationship('estado_migratorio', 'nombre')
                                    ->searchable()
                                    ->helperText('Elija entre las siguientes opciones: Solo, Conyugue, Dependientes, C&D')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->preload()
                                    ->disabled($disabled),
                                TextInput::make('documento_migratorio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Documento Migratorio')
                                    ->type('number')
                                    ->helperText('Si esta en proceso migratorio que documento tiene'),
                                Select::make('tipo_trabajo')
                                    ->helperText('Tipo de trabajo W2 o 1099')
                                    ->options([
                                        '1099' => '1099',
                                        'W2' => 'W2',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->default('1099')
                                    ->native(false),
                                Select::make('quien_aporta_ingresos')
                                    ->helperText('Quíen aporta los ingresos del hogar?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                Select::make('quien_declara_taxes')
                                    ->helperText('Como declara los impuestos (Taxes)?')
                                    ->options([
                                        'Solo' => 'Solo',
                                        'Conyugue' => 'Conyugue',
                                        'Juntos' => 'Juntos',
                                    ])
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->required()
                                    ->native(false),
                                TextInput::make('total_ingresos_gf')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->label('Total ingresos gf')
                                    ->type('number')
                                    ->placeholder('Total ingresos del grupo familiar'),
                                Select::make('compania_aseguradora')
                                    ->searchable()
                                    ->helperText('Compañia elegida para dar la cobertura')
                                    ->options(fn (Get $get): Collection => $get('compania_id') ?
                                        Compania::all()
                                            ->where('id', $get('compania_id'))
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias') :
                                        Compania::all()
                                            ->sortBy('nombre_companias')
                                            ->pluck('nombre_companias', 'nombre_companias')
                                    )
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled),
                                TextInput::make('plan_compania_aseguradora')
                                    ->placeholder('Plan seleccionado por el cliente')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('prima_mensual')
                                    ->label('Prima mensual')
                                    ->placeholder('Prima mensual')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->type('number'),
                                TextInput::make('deducible')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('maximo_bolsillo')
                                    ->label('Maximo bolsillo')
                                    ->placeholder('Máximo de bolsillo')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medicamento_generico')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_primario')
                                    ->label('Medico primario')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('medico_especialista')
                                    ->label('Medico especialista')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('sala_emergencia')
                                    ->placeholder('Sala de emergencia')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                                TextInput::make('subsidio')
                                    ->label('Subsidio')
                                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                                    ->disabled($disabled)
                                    ->maxLength(255),
                            ];
                        }

                        if ($get('personas_aseguradas') && $get('personas_aseguradas') === 'Conyugue y Dependientes') {
                            return [
                                //
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
                                    ->label('Cobertura Anterior')
                                    ->native(false)
                                    ->options([
                                        'Si' => 'Si',
                                        'No' => 'No',
                                        'Xinfo' => 'Xinfo',
                                    ]),
                                TextInput::make('ultimo_agente')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->maxLength(255),
                                DatePicker::make('fecha_retiro')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->native(false),
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
                                DatePicker::make('inicio_cobertura_vig')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
                                    ->native(false),
                                DatePicker::make('fin_cobertura_vig')
                                    ->disabled(! auth()->user()->can('EsBenefit'))
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
                TextInput::make('image')
                    ->label('Imagen')
                    ->disabled(! auth()->user()->can('EsBenefit'))
                    ->url()
                    ->suffixIcon('heroicon-m-globe-alt'),
                Textarea::make('nota_benefit')
                    ->label('Nota benefit')
                    ->placeholder('Nota del benefit')
                    ->disabled(! auth()->user()->can('EsBenefit')),
                Textarea::make('nota_procesador')
                    ->label('Nota procesador')
                    ->placeholder('Nota del procesador')
                    ->disabled(! auth()->user()->can('EsProcesador')),
                Textarea::make('nota_digitadora')
                    ->label('Nota digitador')
                    ->placeholder('Nota del digitador')
                    ->disabled(! auth()->user()->can('EsDigitador')),
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
                TextColumn::make('telefono')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('email')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('nombre1')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('nombre2')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('apellido1')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('apellido2')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('aplica_cobertura')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('fec_nac')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('direccion')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('codigopostal')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('estado.nombre')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('condado.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('ciudad.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('personas_aseguradas')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('nombre_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('aplica_covertura_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fec_nac_conyugue')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('dependientes.nombre_dependiente')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
                    ->searchable(),
                TextColumn::make('dependientes_fuera_pareja')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('estado_migratorio.nombre')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('documento_migratorio')
                    ->hidden(! auth()->user()->hasRole(['digitador', 'admin']))
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
                TextColumn::make('compania_aseguradora')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('plan_compania_aseguradora')
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
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('ultimo_agente')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('fecha_retiro')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('agente')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('inicio_cobertura_vig')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('fin_cobertura_vig')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('imagen')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('nota_benefit')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('nota_procesador')
                    ->hidden(! auth()->user()->hasRole(['procesador']))
                    ->searchable(),
                TextColumn::make('nota_digitadora')
                    ->hidden(! auth()->user()->hasRole(['digitadora']))
                    ->searchable(),
                TextColumn::make('estado_cliente')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('digitador.digitador')
                    ->hidden(! auth()->user()->hasRole(['digitador']))
                    ->searchable(),
                TextColumn::make('fecha_digitadora')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('benefit.benefit')
                    ->hidden(! auth()->user()->hasRole(['benefit']))
                    ->searchable(),
                TextColumn::make('fecha_benefit')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('procesador.procesador')
                    ->hidden(! auth()->user()->hasRole(['procesador']))
                    ->searchable(),
                TextColumn::make('fecha_procesador')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('compania.nombre_companias')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('inicio_cobertura')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fin_cobertura')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('codigo_anterior')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('fecha_retiro_cobertura_ant')
                    ->hidden()
                    ->searchable(),
                TextColumn::make('estado_civil_conyugue')
                    ->hidden()
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
