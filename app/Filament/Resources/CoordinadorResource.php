<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Condado;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\DigitadorCoordinador;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CoordinadorResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;

class CoordinadorResource extends Resource
{
	protected static ?string $model = User::class;

	protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

	protected static ?string $navigationLabel = 'Coordinadores';

	protected static ?string $title = 'Coordinadores';

	protected ?string $heading = 'Custom Page Heading';

	public static $canCreate = false;

	public function getTitle(): string | Htmlable
	{
    	return __('Custom Page Title');
	}

	public static function canCreate(): bool
   {
      return false;
   }


	public static function form(Form $form): Form
	{
		$edit = isset($form->model->exists) ;
		$auxEdit = $edit ? $form->model->id : '';
		$auxDigitadores = $edit ? DigitadorCoordinador::where('coordinador_id', $auxEdit)->pluck('digitador_id') : [];
		$cont = 0;
		foreach($auxDigitadores as $dig) {
			$aux2[$cont] = $dig;
			$cont++;
		}
		$digitadoresLista = User::query()
			->whereHas('roles', function ($query) {
				$query->where('name', 'digitador');
			})
			->pluck('name', 'id');

		// dump($digitadoresLista);
		// exit;
		return $form
			->schema([
				Section::make()
                    ->schema([
						TextInput::make('name')
							->label('Nombre del Coordinador')
							->required()
							->disabled(),

						Select::make('coordinados')
							->label('Digitadores Asignados')
							->multiple()
							->options(function () {
								// Obtener los IDs de los digitadores ya asignados
								$assignedDigitadorIds = DB::table('digitadores_coordinadores')->pluck('digitador_id');
								// Obtener los digitadores que no están asignados
								return User::whereHas('roles', function ($query) {
									$query->where('name', 'digitador');
								})
								->whereNotIn('id', $assignedDigitadorIds)
								->pluck('name', 'id')
								->toArray();
							}),
					])->columns(2)
			 ]);
	}

	public static function table(Table $table): Table
	{
		$table
			->query(
				User::query()->whereHas('roles', function ($query) {
					$query->where('name', 'coordinador');
				})
			)
			->columns([
				Tables\Columns\TextColumn::make('name')
					->label('Nombre')
					->searchable(),
				Tables\Columns\TextColumn::make('digitador')
					->hidden()
					->label('Digitador')
					->searchable(),
				Tables\Columns\TextColumn::make('benefit')
					->hidden()
					->label('Benefit')
					->searchable(),
				Tables\Columns\TextColumn::make('procesador')
					->hidden()
					->label('Procesador')
					->searchable(),
			])
			->filters([
				  //
			  ]);

			// if (auth()->user()->can('Create User')){
			$table
				->actions([
					Tables\Actions\EditAction::make(),
				])
				->bulkActions([
					Tables\Actions\BulkActionGroup::make([
						//
					]),
				]);
			// }
			$table
				->emptyStateActions([
					Tables\Actions\CreateAction::make(),
				]);

			return $table;
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
			'index' => Pages\ListCoordinadors::route('/'),
			'create' => Pages\CreateCoordinador::route('/create'),
			'edit' => Pages\EditCoordinador::route('/{record}/edit'),
		];
	}
}
