<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Models\Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationBadgeTooltip = 'Number of Orders';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ign')->required()
                    ->label("In-Game Name")
                    ->autofocus(),
                Forms\Components\TextInput::make('id')->required()
                    ->label("Game ID"),
                Forms\Components\Select::make('sender_ign')->required()
                    ->label("Sender")
                    ->options(Models\Account::all()->pluck('ign', 'ign'))
                    ->searchable()
                    ->preload()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn(Set $set, ?string $state) => $set(
                            'sender_id',
                            Models\Account::where(
                                'ign',
                                $state
                            )->value('id')
                        )
                    ),
                Forms\Components\TextInput::make('sender_id')->required()
                    ->label("Sender ID")
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('item_name')->required()
                    ->label("Item")
                    ->options(Models\Item::all()->pluck('name', 'name'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(
                        fn(Set $set, ?string $state) => $set(
                            'item_price',
                            Models\Item::where(
                                'name',
                                $state
                            )->value('price')
                        ),
                    ),
                Forms\Components\TextInput::make('item_price')->required()
                    ->label("Item Price")
                    ->prefix('₱')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('quantity')->required()
                    ->label("Quantity")
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(3)
                    ->live()
                    ->afterStateUpdated(
                        fn(Set $set, ?string $state) => $set(
                            'total_price',
                            fn(Get $get) =>
                            intval($get('item_price')) * intval($state)
                        )
                    ),
                Forms\Components\TextInput::make('total_price')->required()
                    ->label("Total Price")
                    ->prefix('₱')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('month')->required()
                    ->label("Month")
                    ->type('month'),
                Forms\Components\Select::make('status')->required()
                    ->label("Status")
                    ->options([
                        'Ordered' => 'Ordered',
                        'Downpayment' => 'Downpayment',
                        'Followed' => 'Followed',
                        'Friends' => 'Friends',
                        'Waiting for Payment' => 'Waiting for Payment',
                        'Paid' => 'Paid',
                        'Ready to Send' => 'Ready to Send',
                        'Completed' => 'Completed',
                    ])
                    ->searchable()
                    ->default('Ordered')
                    ->preload(),
                Forms\Components\DateTimePicker::make('order_date')
                    ->label("Order Date"),
                Forms\Components\DateTimePicker::make('gift_date')
                    ->label("Gift Date"),
                Forms\Components\DateTimePicker::make('friends_since')
                    ->label("Friends Since"),
                Forms\Components\DateTimePicker::make('date_completed')
                    ->label("Date Completed"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label("Status")
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Ordered' => 'gray',
                        'Downpayment' => 'danger',
                        'Followed' => 'success',
                        'Friends' => 'success',
                        'Waiting for Payment' => 'success',
                        'Paid' => 'success',
                        'Ready to Send' => 'gray',
                        'Completed' => 'success',
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ign')
                    ->label("Customer")
                    ->description(fn(Models\Order $record): string => $record->id)
                    ->copyable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sender_ign')
                    ->label("Sender")
                    ->description(fn(Models\Order $record): string => $record->sender_id)
                    ->copyable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('month')
                    ->label("Month")
                    ->description(fn(Models\Order $record): string => $record->month)
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_name')
                    ->label("Item Details")
                    ->description(fn(Models\Order $record): string => '₱' . $record->item_price)
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label("Quantity")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label("Total Price")
                    ->prefix('₱')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label("Order Date")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gift_date')
                    ->label("Gift Date")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('friends_since')
                    ->label("Friends Since")
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_completed')
                    ->label("Date Completed")
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
            'index' => Pages\ManageOrders::route('/'),
        ];
    }
}
