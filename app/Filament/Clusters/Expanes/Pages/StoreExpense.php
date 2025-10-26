<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphField;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasPage;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class StoreExpense extends Page implements HasActions, HasTable
{
    // use HasSinglePage;

    use InteractsWithActions;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.store-expense';

    protected static ?string $cluster = ExpanesCluster::class;

    protected static ?int $navigationSort = 2;

    public function table(Table $table): Table
    {
        //self::translateConfigureTable();
        // dd(ExpansesType::getGroupName('store'));
        return $table
            ->query(Expense::whereIn('expense_type_id', ExpenseType::where('group', 'store')->pluck('id')))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم المصروف')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type.label')
                    ->label('نوع المنصرف')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->expense_type_id
                            ? $record->type->label
                            : $record->custom_expense_type
                    )
                    ->badge(),

                Tables\Columns\TextColumn::make('payer.name')
                    ->label('الحساب الدافع')
                    ->formatStateUsing(fn($record) => optional($record->payer)->name)
                    ->searchable(),

                /* Tables\Columns\TextColumn::make('beneficiary.name')
                    ->label('الحساب المستفيد')
                    ->formatStateUsing(fn($record) => optional($record->beneficiary)->name)
                    ->searchable(), */

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('المخزن')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('الكمية'),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('سعر الوحدة'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('الإجمالي'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('حالة الدفع')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
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
                ReplicateAction::make(),

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

            ]);
    }

    public static function expenseForm()
    {
        return [
            Grid::make()->columns(2)
                ->schema([

                    Section::make()->schema([
                        // 1. القيمة المخفية لنوع المصروف (Fixed for this page)
                        Forms\Components\Select::make('expense_type')
                            ->live()
                            ->options(ExpenseType::where('group', 'store')->pluck('label', 'id'))
                            ->required()
                            ->columnSpanFull(),


                        // 3. الحساب الدافع (الدفع من حساب)
                        MorphSelect::make('payer_select')
                            ->label('من حساب')
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

                        Forms\Components\Select::make('branch_id')
                            ->label(__('المخزن'))
                            ->relationship('branch', 'name') // يفترض وجود علاقة 'store' في موديل Expense
                            ->required()
                            ->default(fn() => Filament::getTenant()->id),

                        // 5. الكمية / amount (عدد الوحدات المشتراة/الكمية)
                        DecimalInput::make('amount')
                            ->label(__('الكمية'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function ($set, $get, $state) {
                                    $price = $get('unit_price') ?? 0;
                                    $amount = $get('amount') ?? 0;
                                    $set('total_amount', ($amount * $price));
                                }
                            )
                            ->required(),

                        // 6. سعر الوحدة / unit_price
                        DecimalInput::make('unit_price')
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
                            ->required(),

                        DecimalInput::make('total_amount')
                            ->label(__('الصافي')),

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
                            ->label(__('وسيلة الدفع'))
                            ->options(\App\Enums\PaymentOptions::class),

                        // 8. رقم الإشعار/الإيصال
                        Forms\Components\TextInput::make('payment_reference')
                            ->label(__('رقم الإشعار/الإيصال'))
                            ->numeric()
                            ->nullable(),

                        // 9. حالة الدفع (عاجل/مؤجل)
                        Forms\Components\Select::make('is_paid')
                            ->label(__('حالة الدفع'))
                            ->options([1 => 'مدفوع (عاجل)', 0 => 'غير مدفوع (مؤجل)'])
                            ->default(1),

                        // 10. التاريخ
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label(__('تاريخ العملية'))
                            ->default(now()),
                    ])
                        ->columnSpan(2)
                        ->columns(2)
                ])

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
