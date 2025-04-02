<?php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use App\Models\Appointment;
use Faker\Provider\Medical;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\MedicalRecordResource;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateMedicalRecord extends CreateRecord
{
    use HasWizard;

    protected static string $resource = MedicalRecordResource::class;

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
            Step::make('Details')
               ->icon('heroicon-o-information-circle')
                ->schema([
                    Section::make()->schema(MedicalRecordResource::getDetailsFormSchema())->columns(),
                ]),

            Step::make('Vital Sign')
                ->icon('heroicon-o-heart')
                ->schema([
                    Section::make()->schema(MedicalRecordResource::getVitalSign())->columns(),
                ]),

            Step::make('Perscription')
                ->icon('heroicon-o-numbered-list')
                ->schema([
                    Group::make()->schema([
                        MedicalRecordResource::getPerscriptionRepeater(),
                    ]),
                ]),

        ];
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {

        // dd( $data);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {

        Appointment::where('id', $data['appointment_id'])->update(['status' => 'completed']);

        return static::getModel()::create($data);
    }
}
