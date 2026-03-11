<?php

namespace App\Filament\Resources\LapanganResource\Pages;

use App\Filament\Resources\LapanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLapangan extends EditRecord
{
    protected static string $resource = LapanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
