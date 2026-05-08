<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions;

use App\Models\Commerce\CommercialDocument;
use App\Services\Commerce\CommercialDocumentGenerator;
use Filament\Actions\Action;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintDocumentAction
{
    public static function make(CommercialDocument $document): Action
    {
        return Action::make('print')
            ->label('Télécharger PDF')
            ->icon(Phosphor::FilePdf)
            ->color('gray')
            ->action(function () use ($document) {
                $generator = app(CommercialDocumentGenerator::class);
                $pdf = $generator->generatePdf($document);
                $filename = strtolower(str_replace([' ', "'"], ['-', ''], $document->type->getLabel()))
                    .'_'.$document->reference.'.pdf';

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf']
                );
            });
    }
}
