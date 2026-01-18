<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Enums\ExpenseGroup;
use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasSinglePage;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CurrencyExpense extends Page implements HasActions, HasTable
{
    use HasSinglePage;
    use InteractsWithActions;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.store-expense';

    protected static ?string $cluster = ExpanesCluster::class;

    protected static ?int $navigationSort = 102;

    public static function getLocalePath(): string
    {
        return 'expense.' . static::className();
    }

    public function table(Table $table): Table
    {
        self::translateConfigureTable();
        self::translateConfigureForm();

        return $table
            ->query(Expense::types('currency'))
            ->defaultSort('id', 'desc')
            ->modelLabel(__('expense.' . static::className() . '.navigation.model_label'))
            ->columns([
                    Tables\Columns\TextColumn::make('id')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('type.label')
                        ->formatStateUsing(
                            fn($state, $record) => $record->expense_type_id
                            ? $record->type->label
                            : $record->custom_expense_type
                        )
                        ->badge(),

                    Tables\Columns\TextColumn::make('payer.name')
                        // ->formatStateUsing(fn($record) => optional($record->payer)->name)
                        ->searchable(),

                    Tables\Columns\TextColumn::make('beneficiary.name')
                        // ->formatStateUsing(fn($record) => optional($record->beneficiary)->name)
                        ->searchable(),

                    Tables\Columns\TextColumn::make('representative.name')
                        // ->formatStateUsing(fn($record) => optional($record->beneficiary)->name)
                        ->searchable(),

                    // Tables\Columns\TextColumn::make('amount'),

                    // Tables\Columns\TextColumn::make('branch.name')
                    //     ->searchable(),

                    // Tables\Columns\TextColumn::make('amount'),
                    // Tables\Columns\TextColumn::make('unit_price'),
                    Tables\Columns\TextColumn::make('total_amount'),
                    Tables\Columns\IconColumn::make('is_paid')
                        ->boolean(),

                ])
            ->filters([
                    TrashedFilter::make()
                        ->visible(auth()->user()->can('restore_user')),
                ])
            ->recordActions([
                    EditAction::make()
                        ->schema(self::expenseForm()),
                    /* Action::make('edit')
                        ->label('تعديل')
                        ->action(fn($record) => $this->edit($record)), */
                    ReplicateAction::make()
                        ->schema(self::expenseForm()),

                    DeleteAction::make()
                        ->requiresConfirmation(),
                    RestoreAction::make()
                        ->visible(fn($record) => $record->deleted_at),
                    ForceDeleteAction::make()
                        ->visible(fn($record) => $record->deleted_at),
                ])
            ->toolbarActions([
                    CreateAction::make()
                        ->schema($this->expenseForm())
                        ->preserveFormDataWhenCreatingAnother(
                            fn(array $data): array => another_expense($data)
                        ),
                ]);
    }

    public static function expenseForm()
    {
        $type = ExpenseType::where('group', 'currency');

        return [
            Grid::make()
                ->columns(2)
                ->schema([

                        Section::make()->schema([
                            // 1. القيمة المخفية لنوع المصروف (Fixed for this page)
                            Forms\Components\Select::make('expense_type_id')
                                ->options($type->pluck('label', 'id'))
                                ->required()
                                ->label(__('expense.currency_expense.fields.type.label'))
                                ->default($type->first()?->id)
                                ->preload()
                                ->searchable()
                                ->columnSpanFull(),

                            MorphSelect::make('beneficiary_select')
                                ->label(__(self::getLocalePath() . '.fields.beneficiary.label'))
                                ->models([
                                        // 'user' => \App\Models\Company::class,
                                        'customer' => fn() => \App\Models\Customer::per(ExpenseGroup::DEBTORS->value)->get(),
                                    ])
                                ->preload()
                                ->required(),

                            Forms\Components\Hidden::make('beneficiary_id'),
                            Forms\Components\Hidden::make('beneficiary_type'),

                            // 3. الحساب الدافع (الدفع من حساب)
                            MorphSelect::make('payer_select')
                                ->label('من حساب')
                                ->models([
                                        'user' => \App\Models\User::class,
                                        'customer' => \App\Models\Customer::per(ExpenseGroup::CURRENCY->value)->get(),
                                    ])
                                ->required(),

                            Forms\Components\Hidden::make('payer_id'),
                            Forms\Components\Hidden::make('payer_type'),

                            // 2. الحساب المستفيد (إلى) - يفترض أنه حساب يتعلق بالمخزن

                            Select::make('representative_id')
                                ->label(__('المندوب'))
                                ->options(User::sales())
                                ->preload()
                                ->searchable()
                                ->nullable(),

                            // 7. وسيلة الدفع
                            Forms\Components\Select::make('payment_method')
                                ->label(__('وسيلة الدفع'))
                                ->options(\App\Enums\PaymentOptions::class),

                            // 8. رقم الإشعار/الإيصال
                            Forms\Components\TextInput::make('payment_reference')
                                ->label(__('رقم الإشعار/الإيصال'))
                                ->numeric()
                                ->nullable(),
                            /* Forms\Components\Select::make('branch_id')
                                ->label(__('المخزن'))
                                ->relationship('branch', 'name') // يفترض وجود علاقة 'store' في موديل Expense
                                ->required()
                                ->default(fn() => Filament::getTenant()->id), */

                            // 5. الكمية / amount (عدد الوحدات المشتراة/الكمية)

                            // 10. التاريخ
                            Forms\Components\DateTimePicker::make('created_at')
                                ->label(__('تاريخ العملية'))
                                ->default(now()),

                            // 6. سعر الوحدة / unit_price
                            DecimalInput::make('amount')
                                ->label(__('المبلغ المراد تحويله'))
                                ->million()
                                ->required(),

                            // 9. حالة الدفع (عاجل/مؤجل)
                            Forms\Components\Select::make('is_paid')
                                ->label(__('حالة الدفع'))
                                ->options([1 => 'مدفوع (عاجل)', 0 => 'غير مدفوع (مؤجل)'])
                                ->default(1),

                            Forms\Components\Textarea::make('notes')
                                ->label(__('ملاحظات'))
                                ->rows(2)
                                ->columnSpanFull() // يجعل حقل الملاحظات يأخذ عرض العمودين كاملاً
                                ->nullable(),
                        ])
                            ->columnSpan(2)
                            ->columns(2),
                    ]),

        ];
    }
    /* public function save(): void
    {
        try {
            $data = $this->form->getState();

            $user = auth()->user();

            dd( $data);
        } catch (Halt $e) {
            Notification::make()
                ->label(__('user.messages.user_not_updated'))
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title(__('user.messages.user_updated'))
            ->success()
            ->send();
    }



    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('user.actions.save.label'))
                ->submit('save'),
        ];
    } */
}
