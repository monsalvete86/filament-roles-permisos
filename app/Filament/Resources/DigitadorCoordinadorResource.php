<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\DigitadorCoordinador;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DigitadorCoordinadorResource\Pages;
use App\Filament\Resources\DigitadorCoordinadorResource\RelationManagers;

class DigitadorCoordinadorResource extends Resource
{
    protected static ?string $model = DigitadorCoordinador::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        //
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        $resultUsers = User::query();
        dump($resultUsers);
        return $table
            ->query(User::query())
            ->columns([
                TextColumn::make('name'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
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
            'index' => Pages\ListDigitadorCoordinadors::route('/'),
            'create' => Pages\CreateDigitadorCoordinador::route('/create'),
            'edit' => Pages\EditDigitadorCoordinador::route('/{record}/edit'),
        ];
    }
}
