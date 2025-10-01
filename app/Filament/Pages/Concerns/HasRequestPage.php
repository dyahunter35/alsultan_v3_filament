<?php

namespace App\Filament\Pages\Concerns;

use Filament\Forms;
use App\Models\Workflow;
use Illuminate\Support\Carbon;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

trait HasRequestPage
{
    // protected function getFooterWidgets(): array
    // {
    //     $widgets = [];

    //     // $widgets[] = RelatableWorkWidget::class;
    //     $widgets[] = \App\Filament\Resources\Request\RequestFormResource\Widgets\WorkRequestList::class;

    //     $widgets = array_merge($widgets, parent::getFooterWidgets());

    //     return $widgets;
    // }

    // protected function getHeaderWidgets(): array
    // {
    //     $widgets = [];

    //     // to do change action
    //     $widgets[] = \App\Filament\Resources\Request\RequestFormResource\Widgets\WorkRequestAction::class;

    //     $widgets = array_merge($widgets, parent::getHeaderWidgets());

    //     return $widgets;
    // }

    protected function getWorkingAction(): Action
    {
        return Action::make('working')
            ->label(__('locale/work.actions.working.label'))
            ->requiresConfirmation()
            ->action(function () {
                return $this->changeStatus(status: 'working');
            })
            ->color('primary');
    }

    protected function getSteppedAction(): Action
    {
        return Action::make('stopped')
            ->label(__('locale/work.actions.stop.label'))
            ->requiresConfirmation()
            ->modalSubheading(__('locale/work.actions.stop.modal.subheading'))
            ->action(function () {
                return $this->changeStatus(status: 'stopped');
            })
            ->color('warning');
    }

    protected function getClosedAction(): Action
    {
        return Action::make('close')
            ->label(__('locale/work.actions.close.label'))
            ->requiresConfirmation()
            ->modalSubheading(__('locale/work.actions.close.modal.subheading'))
            ->action(function (): void {
                $check_requests_status = $this->record->requestForms()->where('isdeleted', 0)->get()
                    ->map(function ($request) {
                        if (!$request->isComplete()) {
                            return $request;
                        };
                        return null;
                    })
                    ->filter();

                if ($check_requests_status->count()) {
                    Notification::make()
                        ->title(__('locale/work.actions.close.messages.complete_requests_first'))
                        ->warning()
                        ->send();

                    return;
                }

                $this->changeStatus(status: 'closed');
                return;
            })
            ->color('success');
    }

    protected function getCreateProcedureAction(): Action
    {
        return Action::make('create-procedure')
            ->label(__('locale/work.actions.create_procedure.label'))
            ->form([
                Forms\Components\Select::make('workflow')
                    ->options(Workflow::getByModel(get_class($this->record))->pluck(__('locale/layout.name_locale'), 'id'))
                    ->required(),
            ])
            ->action(function ($data): void {

                $procedure = $this->record->startProcedureById($data['workflow'], $this->record->work ?? 'Procedure');

                $this->emit('refreshWorkRequestList');
                $this->emit('refresh');
            })
            ->color('primary');
    }

    protected function getChangeStatusActions(): array
    {
        if (in_array($this->record->status, ['stopped'])) {
            return [
                $this->getWorkingAction(),
                $this->getClosedAction()
            ];
        }

        if (in_array($this->record->status, ['working'])) {
            return [
                $this->getSteppedAction(),
                $this->getClosedAction()
            ];
        }

        if (in_array($this->record->status, ['closed'])) {
            return [
                $this->getWorkingAction(),
            ];
        }

        return [];
    }

    protected function changeStatus($status): void
    {
        if (!auth()->user()->can('Change Works Status')) {
            $this->notify('warning', __('locale/layout.permission.messages.not_have_permission'));
            return;
        }

        if (!in_array($status, ['working', 'stopped', 'closed'])) {
            $this->notify('warning', __('locale/layout.system.messages.error'));
            return;
        }

        DB::beginTransaction();

        $this->record->status = $status;
        $this->record->status_by = auth()->user()->id;
        $this->record->status_at = Carbon::now();

        $record_save = $this->record->save();

        if ($record_save) {
            $this->record->changeStatus();

            DB::commit();

            $this->notify('success', __('locale/layout.system.messages.action.success'));

            // to do enhance refresh action
            $this->callMethod('$refresh');

            $this->emitSelf('refreshViewConstruction');

            $this->emit('refreshWorkRequestList');
            return;
        } else {
            DB::rollBack();

            $this->notify('warning', __('locale/layout.system.messages.error'));
            return;
        }
    }

    protected function getActions(): array
    {
        $actions = parent::getActions();

        // $actions[] = $this->getCreateProcedureAction();

        // return array_merge($actions, $this->getChangeStatusActions());
        return parent::getActions();
    }
}
