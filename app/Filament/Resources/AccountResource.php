<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models;
use App\Models\Account;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components;
use Carbon\Carbon;
use Filament\Forms\Set;
use Filament\Forms\Get;


class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationBadgeTooltip = 'Number of Accounts';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ign')->required()
                    ->label("In-Game Name"),
                Forms\Components\TextInput::make('id')->required()
                    ->label("Game ID"),
                Forms\Components\TextInput::make('server')->required()
                    ->label("Server"),
                Forms\Components\TextInput::make('diamonds')->required()
                    ->label("Diamonds")
                    ->numeric()
                    ->step(20),
                Forms\Components\TextInput::make('wdp_count')->required()
                    ->label("Weekly Diamond Pass")
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(
                        fn(Set $set, ?string $state, Account $record) => $set(
                            'diamonds',
                            fn() =>
                            (intval($state) * 100) + $record->diamonds
                        )
                    ),
                Forms\Components\DateTimePicker::make('start_date')
                    ->label("Subscription Start Date"),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label("Subscription End Date"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ign')
                    ->label("In-Game Name")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('id')
                    ->label("Game ID")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('server')
                    ->label("Server")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('diamonds')
                    ->label("Diamonds")
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('wdp_count')
                    ->label("Weekly Diamond Pass")
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label("Subscription Start Date")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label("Subscription End Date")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('days_left')
                    ->label("Days Left")
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $start_date = new \DateTime($record->start_date);
                        $end_date = new \DateTime($record->end_date);
                        $days_left = $end_date->diff($start_date)->days;

                        return $days_left;
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimated_total_diamonds')
                    ->label("Estimated Total Diamonds")
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return $record->wdp_count * 100 + $record->total_claimed * 20;
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_diamonds_auto')
                    ->label("Diamonds (Auto)")
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        if (Carbon::now()->format('Y-m-d') === Carbon::yesterday()->format('Y-m-d')) {
                            $record->current_diamonds_auto += $record->diamonds + 20;
                            $record->save();
                        }
                        return $record->current_diamonds_auto;
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_diamonds_manual')
                    ->label("Diamonds (Manual)")
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make(Carbon::yesterday()->toDateString())
                    ->label(Carbon::yesterday()->format('M d, Y'))
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record->diamonds += 20;
                            $record->current_diamonds_manual += 20;
                            $record->estimated_total_diamonds += 20;
                            $record->total_claimed += 1;
                        } else {
                            $record->diamonds -= 20;
                            $record->current_diamonds_manual -= 20;
                            $record->estimated_total_diamonds -= 20;
                            $record->total_claimed -= 1;
                        }
                        $record->save();
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make(Carbon::today()->toDateString())
                    ->label(Carbon::today()->format('M d, Y'))
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record->diamonds += 20;
                            $record->current_diamonds_manual += 20;
                            $record->estimated_total_diamonds += 20;
                            $record->total_claimed += 1;
                        } else {
                            $record->diamonds -= 20;
                            $record->current_diamonds_manual -= 20;
                            $record->estimated_total_diamonds -= 20;
                            $record->total_claimed -= 1;
                        }
                        $record->save();
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make(Carbon::tomorrow()->toDateString())
                    ->label(Carbon::tomorrow()->format('M d, Y'))
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record->diamonds += 20;
                            $record->current_diamonds_manual += 20;
                            $record->estimated_total_diamonds += 20;
                            $record->total_claimed += 1;
                        } else {
                            $record->diamonds -= 20;
                            $record->current_diamonds_manual -= 20;
                            $record->estimated_total_diamonds -= 20;
                            $record->total_claimed -= 1;
                        }
                        $record->save();
                    })
                    ->toggleable()
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAccounts::route('/'),
        ];
    }
}
