<?php

namespace App\Filament\Resources\SalaryAdvances;

use App\Models\SalaryAdvance;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalaryAdvanceResource extends Resource
{
    protected static ?string $model = SalaryAdvance::class;
    protected static bool $shouldRegisterNavigation = false;

    #protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    // protected static ?string $navigationGroup = 'الرواتب';
    // protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'سلفة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'السلفيات';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('الموظف')
                        ->options(User::role('employee')->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('amount')
                        ->label('المبلغ')
                        ->required()
                        ->numeric()
                        ->prefix('ر.س'),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('payer_id')
                        ->default(fn() => Auth::id()),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    Tables\Columns\TextColumn::make('created_at')
                        ->label('التاريخ')
                        ->dateTime()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('employee.name')
                        ->label('الموظف')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('amount')
                        ->label('المبلغ')
                        ->money('SAR')
                        ->sortable(),

                    Tables\Columns\IconColumn::make('is_recovered')
                        ->label('تم التحصيل')
                        ->boolean(),

                    Tables\Columns\TextColumn::make('payer.name')
                        ->label('بواسطة')
                        ->toggleable(isToggledHiddenByDefault: true),

                    Tables\Columns\TextColumn::make('notes')
                        ->label('ملاحظات')
                        ->limit(30)
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->filters([
                    Tables\Filters\SelectFilter::make('employee_id')
                        ->label('الموظف')
                        ->options(User::role('employee')->pluck('name', 'id')),

                    Tables\Filters\TernaryFilter::make('is_recovered')
                        ->label('حالة التحصيل'),
                ])
            ->actions([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ->bulkActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSalaryAdvances::route('/'),
            // 'create' => Pages\CreateSalaryAdvance::route('/create'),
            // 'edit' => Pages\EditSalaryAdvance::route('/{record}/edit'),
        ];
    }
}
