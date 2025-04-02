<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PrescriptionResource;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreatePrescription extends CreateRecord
{

    use HasWizard;

    protected static string $resource = PrescriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }


    /** @return Step[] */
    protected function getSteps(): array
    {
        return [
            Step::make('Prescription Details')
               ->icon('heroicon-o-information-circle')
                ->schema([
                    Section::make()->schema(PrescriptionResource::getPresDetailsFormSchema())->columns(),
                ]),

            Step::make('Medications')
                ->icon('heroicon-o-battery-100')
                ->schema([
                    Section::make()->schema([
                        PrescriptionResource::getMedications()
                    ]),
                ]),

        ];
    }

}
