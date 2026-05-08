<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages;

use App\Enums\Commerce\DocumentType;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Models\Commerce\CommercialDocument;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCommercialDocuments extends ListRecords
{
    protected static string $resource = CommercialDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_devis')
                ->label('Nouveau devis')
                ->icon('heroicon-o-plus')
                ->color('info')
                ->url(static::getResource()::getUrl('create', ['type' => DocumentType::DEVIS->value])),

            Action::make('new_facture')
                ->label('Nouvelle facture')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(static::getResource()::getUrl('create', ['type' => DocumentType::FACTURE->value])),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->badge(fn () => CommercialDocument::count()),

            'devis' => Tab::make('Devis')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', DocumentType::DEVIS))
                ->badge(fn () => CommercialDocument::where('type', DocumentType::DEVIS)->count())
                ->badgeColor('info'),

            'bdc' => Tab::make('Bons de commande')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', DocumentType::BON_COMMANDE))
                ->badge(fn () => CommercialDocument::where('type', DocumentType::BON_COMMANDE)->count())
                ->badgeColor('primary'),

            'bl' => Tab::make('Bons de livraison')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', DocumentType::BON_LIVRAISON))
                ->badge(fn () => CommercialDocument::where('type', DocumentType::BON_LIVRAISON)->count())
                ->badgeColor('warning'),

            'factures' => Tab::make('Factures')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', [DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE]))
                ->badge(fn () => CommercialDocument::whereIn('type', [
                    DocumentType::FACTURE,
                    DocumentType::FACTURE_ACOMPTE,
                ])->count())
                ->badgeColor('success'),

            'impayes' => Tab::make('Impayés')
                ->modifyQueryUsing(fn (Builder $query) => $query->impayes())
                ->badge(fn () => CommercialDocument::impayes()->count())
                ->badgeColor('danger'),

            'avoirs' => Tab::make('Avoirs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', DocumentType::AVOIR))
                ->badge(fn () => CommercialDocument::where('type', DocumentType::AVOIR)->count())
                ->badgeColor('danger'),
        ];
    }
}
