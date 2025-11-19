<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class Profile extends EditProfile
{

    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.pages.auth.profile';

    public ?array $data = [];

    public function mount(): void
    {
        // تهيئة النموذج ببيانات المستخدم الحالي
        $user = auth()->user();
        $this->form->fill(
            [
                'name' => $user->getRawOriginal('name'),
                'email' => $user->email
            ]
        );
    }




    protected function getFormSchema(): array
    {
        return [
            Section::make('معلومات الحساب الأساسية')
                ->description('تحديث الاسم وعنوان البريد الإلكتروني الخاص بك.')
                ->schema([
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->afterStateUpdated(fn($record) => $record->getRawOriginal('name'))
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        // استخدم unique مع تجاهل المستخدم الحالي
                        ->unique(table: 'users', ignoreRecord: auth()->user()->id)
                        ->maxLength(255),

                ])->columns(2),


            Section::make('تغيير كلمة المرور')
                ->description('ملء هذه الحقول الثلاثة مطلوب لتغيير كلمة المرور.')
                ->schema([
                    // **كلمة المرور القديمة (للتأكيد)**
                    TextInput::make('current_password')
                        ->label('كلمة المرور الحالية')
                        ->password()
                        ->requiredIf('new_password', fn($get) => filled($get('new_password')))

                        // احتفظ بالقاعدة المخصصة للتحقق من تطابق كلمة المرور
                        ->rules([
                            function ($attribute, $value, $fail) {
                                // التحقق يتم فقط إذا تم ملء الحقل
                                if (filled($value) && !Hash::check($value, auth()->user()->password)) {
                                    $fail('كلمة المرور الحالية غير صحيحة.');
                                }
                            }
                        ]),

                    // **كلمة المرور الجديدة**
                    TextInput::make('new_password')
                        ->label('كلمة المرور الجديدة')
                        ->password()
                        // 1. إزالة required()
                        ->confirmed()
                        // 2. استخدم required_with:current_password ليصبح مطلوباً إذا تم ملء الحقل القديم
                        ->requiredWith('current_password')
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null) // تشفير الكلمة فقط إذا كانت موجودة
                        ->rule(Password::default()),

                    // **تأكيد كلمة المرور الجديدة**
                    TextInput::make('new_password_confirmation')
                        ->label('تأكيد كلمة المرور الجديدة')
                        ->password()
                        // 1. إزالة required()
                        // 2. استخدم required_with:new_password ليتطابق مع الحقل الجديد
                        ->requiredWith('new_password')
                        ->dehydrated(false), // لا يتم حفظها في قاعدة البيانات
                ])->columns(2),
        ];
    }
    protected function getFormModel(): string
    {
        // يربط النموذج بشكل مباشر بمستخدم المصادقة
        return get_class(auth()->user());
    }

    // لربط الحقول بالبيانات
    public function form(Schema $schema): Schema
    {
        return
            $schema
            ->model(auth()->user())
            ->schema($this->getFormSchema())
        ;
    }

    // دالة المعالجة عند الضغط على زر الحفظ
    public function submit(): void
    {
        try {
            // التحقق من صحة جميع المدخلات
            $data = $this->form->getState();

            // فصل حقول كلمة المرور للتحديث المنفصل
            $passwordFields = [
                'current_password' => $data['current_password'],
                'new_password' => $data['new_password'],
            ];

            // إزالة حقول كلمة المرور من بيانات التحديث الأساسية
            unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);


            // تحديث بيانات المستخدم الأساسية (الاسم، البريد الإلكتروني، الصورة)
            auth()->user()->update($data);

            // تحديث كلمة المرور الجديدة إذا تم إدخالها
            if (!empty($passwordFields['new_password'])) {
                auth()->user()->update([
                    'password' => $passwordFields['new_password'], // تم التشفير بالفعل في dehydrateStateUsing
                ]);
            }

            // إرسال إشعار بالنجاح
            \Filament\Notifications\Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم تحديث ملفك الشخصي.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('حدث خطأ')
                ->body('لم يتمكن النظام من تحديث البيانات.')
                ->danger()
                ->send();
        }
    }
}
