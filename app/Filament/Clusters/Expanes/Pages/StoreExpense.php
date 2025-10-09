<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Pages\Concerns\HasPage;
use App\Models\Expense;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class StoreExpense extends Page implements HasActions, HasSchemas, HasTable
{
    use HasPage;

    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.store-expense';

    protected static ?string $cluster = ExpanesCluster::class;

    public ?array $data = [];

    public ?Model $editingRecord = null;

    public $stats;
    public $loading = true;

    public function getTitle(): string | Htmlable
    {
        return __('user.profile.edit.label');
    }

    public function getBreadcrumb(): string | Htmlable
    {
        return __('user.profile.edit.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.profile.label');
    }
    public function mount(): void
    {
        $user = auth()->user()->toArray();
        // Load current authenticated user
        $this->form->fill(
            $user
        );

        //dd($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('name')
            ])
        ;
    }

    public function create(): void
    {
        dd($this->form->getState());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
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

                Action::make('edit')
                    ->action(fn($record) => $this->form->fill($record->attributesToArray())),
            ])
            ->toolbarActions([]);
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
