<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Enums\ExpenseGroup;
use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasSinglePage;
use App\Models\Expense;
use App\Models\ExpenseType;
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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ShipmentExpense extends Page implements HasActions, HasTable
{
    use HasSinglePage;
    use InteractsWithActions;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.store-expense';

    protected static ?string $cluster = ExpanesCluster::class;

    protected static ?int $navigationSort = 108;

    public static function getLocalePath(): string
    {
        return 'expense.'.static::className();
    }

    public function table(Table $table): Table
    {
        self::translateConfigureTable();

        // dd(ExpansesType::getGroupName('store'));
        return $table
            ->query(Expense::types(ExpenseGroup::SHIPMENT_CLEARANCE))
            ->defaultSort('id', 'desc')
            ->modelLabel(__('expense.'.static::className().'.navigation.model_label'))
            ->columns(
                ShipmentExpense::expenseTableColumns()
            )
            ->filters([
                TrashedFilter::make()
                    ->visible(auth()->user()->can('restore_expense')),
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
                    ->visible(fn ($record) => $record->deleted_at),
                ForceDeleteAction::make()
                    ->visible(fn ($record) => $record->deleted_at),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->schema($this->expenseForm())
                    ->preserveFormDataWhenCreatingAnother(
                        fn (array $data): array => another_expense($data)
                    ),
            ]);
    }

    public static function expenseTableColumns()
    {
        return
            [
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make(name: 'code')
                    ->label('رقم الشاحنة')
                    ->sortable()
                    ->searchable(),
                /* Tables\Columns\TextColumn::make('branch.name')
                    ->searchable(), */

                Tables\Columns\TextColumn::make('type.label')
                    ->formatStateUsing(
                        fn ($state, $record) => $record->expense_type_id
                            ? $record->type->label
                            : $record->custom_expense_type
                    )
                    ->badge(),

                Tables\Columns\TextColumn::make('payer.name')
                    ->formatStateUsing(fn ($record) => optional($record->payer)->name)
                    ->searchable(),

                /* Tables\Columns\TextColumn::make('beneficiary.name')
                    ->label('الحساب المستفيد')
                    ->formatStateUsing(fn($record) => optional($record->beneficiary)->name)
                    ->searchable(), */

                // Tables\Columns\TextColumn::make('amount'),

                Tables\Columns\TextColumn::make('total_amount'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ];
    }

    public static function expenseForm($truckId = null)
    {
        return [
            Grid::make()->columns(2)
                ->schema([

                    Section::make()->schema([
                        // 1. القيمة المخفية لنوع المصروف (Fixed for this page)
                        Forms\Components\Select::make('expense_type_id')
                            ->label(__(self::getLocalePath().'.fields.type.label'))
                            ->live()
                            ->options(ExpenseType::where('group', ExpenseGroup::SHIPMENT_CLEARANCE)->pluck('label', 'id'))
                            ->required()
                            ->createOptionForm([
                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('label')
                                            ->label(__('expense_type.fields.label.label'))
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($set, $state) => $set('key', Str::slug($state)))
                                            ->required(),

                                        TextInput::make('key')
                                            ->readOnly()
                                            ->label(__('expense_type.fields.key.label'))
                                            ->required(),

                                        Hidden::make('group')
                                            ->label(__('expense_type.fields.group.label'))
                                            ->default(ExpenseGroup::SHIPMENT_CLEARANCE->value),
                                    ]),

                            ])
                            ->createOptionUsing(function ($data, $set) {
                                ExpenseType::create($data);
                                Notification::make()
                                    ->body('create successfully')
                                    ->send();
                            })
                            ->reactive()
                            ->createOptionAction(fn (Action $action) => $action
                                ->modalHeading(__('customer.actions.create.modal.heading'))
                                ->modalSubmitActionLabel(__('customer.actions.create.modal.submit'))
                                ->modalWidth('lg'))
                            ->columnSpanFull(),

                        // 3. الحساب الدافع (الدفع من حساب)
                        MorphSelect::make('payer_select')
                            ->label(__(self::getLocalePath().'.fields.payer.label'))
                            ->models([
                                'user' => \App\Models\User::class,
                                'customer' => \App\Models\Customer::class,
                            ])
                            ->required(),

                        Forms\Components\Hidden::make('payer_id'),
                        Forms\Components\Hidden::make('payer_type'),

                        // 2. الحساب المستفيد (إلى) - يفترض أنه حساب يتعلق بالمخزن

                        /*  MorphSelect::make('beneficiary_select')
                            ->label('الي حساب')
                            ->models([
                                'user' => \App\Models\User::class,
                                'customer' => \App\Models\Customer::class,
                            ])
                            ->required(),

                        Forms\Components\Hidden::make('beneficiary_id'),
                        Forms\Components\Hidden::make('beneficiary_type'), */

                        /* Forms\Components\Select::make('branch_id')
                            ->label(__(self::getLocalePath() . '.fields.branch.label'))
                            ->relationship('branch', 'name') // يفترض وجود علاقة 'store' في موديل Expense
                            ->required()
                            ->default(fn() => Filament::getTenant()->id), */

                        Forms\Components\Select::make('truck_id')
                            ->label(__('الشاحنة'))
                            ->relationship('truck', 'id') // يفترض وجود علاقة 'store' في موديل Expense
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default($truckId),

                        // 5. الكمية / amount (عدد الوحدات المشتراة/الكمية)
                        /* DecimalInput::make('amount')
                            ->label(__('الكمية'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function ($set, $get, $state) {
                                    $price = $get('unit_price') ?? 0;
                                    $amount = $get('amount') ?? 0;
                                    $set('total_amount', ($amount * $price));
                                }
                            )
                            ->required(), */

                        // 6. سعر الوحدة / unit_price
                        /* DecimalInput::make('unit_price')
                            ->label(__('سعر الوحدة'))
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function ($set, $get, $state) {
                                    $price = $get('unit_price') ?? 1;
                                    $amount = $get('amount') ?? 1;
                                    $set('total_amount', ($amount * $price));
                                }
                            )
                            ->required(), */

                        DecimalInput::make('amount')
                            ->label(__(self::getLocalePath().'.fields.total_amount.label'))
                            ->million()
                            ->required(),

                        // 11. الملاحظات
                        Forms\Components\Textarea::make('notes')
                            ->label(__('ملاحظات'))

                            ->rows(2)
                            ->columnSpanFull() // يجعل حقل الملاحظات يأخذ عرض العمودين كاملاً
                            ->nullable(),

                    ])
                        ->columns(2)
                        ->columnSpan(2),
                    Section::make()->schema([
                        // 7. وسيلة الدفع
                        Forms\Components\Select::make('payment_method')
                            ->label(__(self::getLocalePath().'.fields.payment_method.label'))
                            ->options(\App\Enums\PaymentOptions::class),

                        // 8. رقم الإشعار/الإيصال
                        Forms\Components\TextInput::make('payment_reference')
                            ->label(__(self::getLocalePath().'.fields.payment_reference.label'))
                            ->numeric()
                            ->nullable(),

                        // 9. حالة الدفع (عاجل/مؤجل)
                        Forms\Components\Select::make('is_paid')
                            ->label(__(self::getLocalePath().'.fields.is_paid.label'))
                            ->options([1 => 'مدفوع (عاجل)', 0 => 'غير مدفوع (مؤجل)'])
                            ->default(1),

                        // 10. التاريخ
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label(__('تاريخ العملية'))
                            ->default(now()),
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
