<?php

namespace App\Filament\Resources\Contracts\RelationManagers;

use App\Filament\Pages\Concerns\HasRelationManager;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DocumentsRelationManager extends RelationManager
{
    use HasRelationManager;
    protected static string $relationship = 'documents';

    public function form(Schema $form): Schema
    {
        static::translateConfigureForm();
        return $form
            ->schema([
                Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('issuance_date')
                            ->required(),

                        Forms\Components\TextInput::make('type')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('note')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->columns(2),

                Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('file')
                            ->collection('contract_docs')
                            ->multiple()
                            ->required()
                            ->columnSpanFull()
                            ->openable()
                            //->deletable(true)
                            //->model(fn() => $this->ownerRecord)
                            ->maxSize(10240) // 10MB
                            ->downloadable(),
                    ])->columnSpan(1)

            ])->columns(3);
    }

    public function infolist(Schema $schema): Schema
    {
        static::translateConfigureInfolist();
        return $schema
            ->components([

                TextEntry::make('name'),
                TextEntry::make('issuance_date')->date(),
                SpatieMediaLibraryImageEntry::make('file')
                    ->label('File')
                    ->collection('truck_docs')

                    ->conversion('thumb')
            ]);
    }

    public function table(Table $table): Table
    {
        static::translateConfigureTable();
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('file')
                    ->collection('contract_docs')
                    ->conversion('thumbnail')
                    ->circular()
                    ->height(50)
                    ->width(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('issuance_date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable()
            ])->filters([
                DateRangeFilter::make('issuance_date')->opens(OpenDirection::RIGHT),
            ])
            ->headerActions([
                CreateAction::make(),
            ])

            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
                //ForceDeleteAction::make(),
                //RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                    //ForceDeleteBulkAction::make(),
                    //RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
