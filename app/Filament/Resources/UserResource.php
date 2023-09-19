<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationGroup = 'Configuraciones';

  public static function form(Form $form): Form
  {
      return $form
          ->schema([
              Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),
              Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
              Forms\Components\Select::make('roles')
                ->label('Rol')
                ->required()
                ->searchable()
                ->relationship('roles', 'name'),
              Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),
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
              /* Tables\Columns\TextColumn::make('created_at')
                  ->dateTime()
                  ->sortable()
                  ->toggleable(isToggledHiddenByDefault: true), */
              Tables\Columns\TextColumn::make('roles.name')->sortable()->searchable()
          ])
          ->filters([
              //
          ]);
      dump(auth()->user()->roles());
      if (auth()->user()->can('Create User')){ 
        $table
          ->actions([
              Tables\Actions\EditAction::make(),
          ])
          ->bulkActions([
              Tables\Actions\BulkActionGroup::make([
                  Tables\Actions\DeleteBulkAction::make(),
              ]),
            ]);
        }      
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
