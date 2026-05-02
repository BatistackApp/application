<?php

namespace App\Filament\Article\Resources\Article\Articles\Schemas;

use App\Enums\Article\TrackingType;
use App\Models\Article\Article;
use chillerlan\QRCode\QRCode;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(fn (Article $article) => ! empty($article->barcode) || ! empty($article->qr_code_base) ? 3 : 2)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sku')
                                    ->label('Code'),

                                TextEntry::make('articleCategory.name')
                                    ->label('Catégorie')
                                    ->icon(Heroicon::Tag),

                                TextEntry::make('supplier.name')
                                    ->label('Fournisseur')
                                    ->icon(Heroicon::UserCircle)
                                    ->formatStateUsing(function (Article $article) {
                                        return empty($article->supplier->name) ? 'Aucun Fournisseur' : $article->supplier->name;
                                    }),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('unit')
                                    ->color('info')
                                    ->label('Unité de Vente/Achat'),

                                TextEntry::make('tracking_type')
                                    ->color('info')
                                    ->tooltip(fn (TrackingType $state) => $state->getDescription())
                                    ->label('Type de tracking'),
                            ]),

                        Grid::make(1)
                            ->visible(fn (Article $article) => ! empty($article->barcode) || ! empty($article->qr_code_base))
                            ->schema([
                                TextEntry::make('barcode')
                                    ->label('Codebar EAN13')
                                    ->formatStateUsing(function (Article $article) {
                                        $qrCode = (new QRCode)->render($article->qr_code_base);
                                        $barcodeSVG = (BarcodeGeneratorSVG::class)->getBarcode($article->barcode ?? $article->sku, BarcodeGenerator::TYPE_EAN_13, 2, 45);

                                        return new HtmlString("
                                            <div class='flex items-center gap-6 p-4 bg-white rounded-xl border border-gray-200 w-fit shadow-sm'>
                                                <!-- QR Code -->
                                                <div class='w-20 h-20'>
                                                    {$qrCode}
                                                </div>

                                                <!-- Séparateur -->
                                                <div class='h-16 w-px bg-gray-100'></div>

                                                <!-- Barcode -->
                                                <div class='flex flex-col items-center gap-2'>
                                                    <div class='h-[45px]'>
                                                        {$barcodeSVG}
                                                    </div>
                                                </div>
                                            </div>
                                        ");
                                    }),
                            ]),
                    ]),
            ]);
    }
}
