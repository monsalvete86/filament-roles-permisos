<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
	protected static ?string $model = User::class;

	protected static ?string $navigationIcon = 'heroicon-o-users';

	protected static ?string $navigationGroup = 'Configuración del Sistema';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Section::make()
                    ->schema([
						Forms\Components\TextInput::make('name')
							->label('Nombre')
							->required()
							->maxLength(255),
						Forms\Components\TextInput::make('email')
							->email()
							->required()
							->maxLength(255),
						Forms\Components\TextInput::make('password')
							->password()
							->dehydrateStateUsing(fn ($state) => Hash::make($state))
							->dehydrated(fn ($state) => filled($state))
							->required(fn (string $context): bool => $context === 'create'),
						Forms\Components\Select::make('roles')
							->label('Rol')
							->required()
							->searchable()
							->relationship('roles', 'name'),
					])->columns(2)
			 ]);
	}

	public static function table(Table $table): Table
	{
		$table
			->columns([
				Tables\Columns\TextColumn::make('name')
					->label('Nombre')
					->searchable(),
				Tables\Columns\TextColumn::make('email')
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
				Tables\Columns\TextColumn::make('roles.name')
					->sortable()
					->searchable(),
			])
			->filters([
				  //
			  ]);

			// if (auth()->user()->can('Create User')){
			$table
				->actions([
					Tables\Actions\EditAction::make(),
					Tables\Actions\DeleteAction::make(),
				])
				->bulkActions([
					Tables\Actions\BulkActionGroup::make([
						Tables\Actions\DeleteBulkAction::make(),
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
			'index' => Pages\ListUsers::route('/'),
			'create' => Pages\CreateUser::route('/create'),
			'edit' => Pages\EditUser::route('/{record}/edit'),
		];
	}
}
