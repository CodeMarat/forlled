<?php

namespace App\Filament\Resources\TreatmentResource\Pages;

use App\Filament\Resources\TreatmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class TreatmentsPage extends ManageRecords
{
    protected static string $resource = TreatmentResource::class;
    public function __construct()
    {
        dd('asd');
    }
}
