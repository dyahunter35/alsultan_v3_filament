<?php

namespace App\Filament\Resources\Users;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-m-users';
    protected static ?int $navigationSort = 1;
    protected static bool $isScopedToTenant = false;

    public static function getModelLabel(): string
    {
        return __('user.navigation.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.navigation.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('user.navigation.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('user.sections.general'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('user.fields.name.label'))
                            ->placeholder(__('user.fields.name.placeholder'))
                            ->required()
                            ->afterStateUpdated(fn(?Model $record) => $record)

                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('user.fields.email.label'))
                            ->placeholder(__('user.fields.email.placeholder'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('كلمة السر')
                            ->placeholder('أدخل كلمة السر الجديدة')
                            ->password()                          // يخفي النص المدخل
                            ->required(fn($record) => !$record)
                            ->revealable()                        // زر لإظهار/إخفاء كلمة السر
                            ->rule(
                                Password::default()          // الحد الأدنى للطول
                                //->mixedCase()         // يجب أن تحتوي على حروف كبيرة وصغيرة
                                //->letters()           // يجب أن تحتوي على حروف
                                //->numbers()           // يجب أن تحتوي على أرقام
                                //->symbols()           // يجب أن تحتوي على رموز
                                //->uncompromised()     // تتحقق من عدم تسريبها في خروقات
                            )->dehydrated(fn($state) => filled($state))  // حفظ فقط إذا تم تعبئتها
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)) // تشفير قبل الحفظ
                        //->helperText('يجب أن تحتوي كلمة السر على 8 أحرف على الأقل، أحرف كبيرة وصغيرة، أرقام ورموز.')

                    ])->columnSpan(2)
                    ->columns(2),
                Section::make(__('user.sections.roles'))
                    ->schema([

                        Select::make('roles')
                            ->label(__('user.fields.roles.label'))
                            ->placeholder(__('user.fields.roles.placeholder'))
                            ->relationship('roles', 'name')
                            ->saveRelationshipsUsing(function (Model $record, $state) {
                                $record->roles()->sync($state);
                            })
                            ->visible(fn() => auth()->user()->hasRole('super_admin'))
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Select::make('branch')
                            ->label(__('user.fields.branch.label'))
                            ->placeholder(__('user.fields.branch.placeholder'))
                            ->relationship('branch', 'name')
                            ->saveRelationshipsUsing(function (Model $record, $state) {
                                $record->branch()->sync($state);
                            })
                            ->rules(['array', 'min:1'])
                            // (اختياري ولكن موصى به) رسالة خطأ مخصصة
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('user.fields.name.label'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('user.fields.email.label'))
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label(__('user.fields.roles.label'))
                    ->searchable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label(__('user.fields.branch.label'))
                    ->searchable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('user.fields.created_at.label'))

                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('user.fields.updated_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->visible(auth()->user()->can('restore_user')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn(User $user) => (auth()->user()->hasRole('super_admin') || $user->id == auth()->user()->id) || $user->deleted_at),

                RestoreAction::make()
                    ->visible(fn($record) => $record->deleted_at),
                ForceDeleteAction::make()
                    ->visible(fn($record) => $record->deleted_at),
            ])
            ->toolbarActions([
                Action::make('export_pdf')
                    ->label('Export PDF')
                    // ->icon('heroicon-o-pdf')
                    ->action(function (\Filament\Tables\Contracts\HasTable $livewire) {
                        // جلب بيانات الجدول حسب الفلترة الحالية
                        $data = $livewire->getFilteredTableQuery()->get();

                        $pdf = Pdf::loadView('filament.resources.user-resource.reports.users-print', compact('data'));

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'users.pdf'
                        );
                    }),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
