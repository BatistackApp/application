<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum DocumentType: string implements HasColor, HasIcon, HasLabel
{
    case DEVIS = 'devis';
    case BON_COMMANDE = 'bon_commande';
    case BON_LIVRAISON = 'bon_livraison';
    case FACTURE = 'facture';
    case FACTURE_ACOMPTE = 'facture_acompte';
    case AVOIR = 'avoir';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::DEVIS => 'Devis',
            self::BON_COMMANDE => 'Bon de commande',
            self::BON_LIVRAISON => 'Bon de livraison',
            self::FACTURE => 'Facture',
            self::FACTURE_ACOMPTE => 'Facture d\'acompte',
            self::AVOIR => 'Avoir',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DEVIS => 'info',
            self::BON_COMMANDE => 'primary',
            self::BON_LIVRAISON => 'warning',
            self::FACTURE, self::FACTURE_ACOMPTE => 'success',
            self::AVOIR => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DEVIS => Phosphor::FileText,
            self::BON_COMMANDE => Phosphor::ShoppingCart,
            self::BON_LIVRAISON => Phosphor::Package,
            self::FACTURE => Phosphor::Receipt,
            self::FACTURE_ACOMPTE => Phosphor::CurrencyEur,
            self::AVOIR => Phosphor::ArrowCounterClockwise,
        };
    }

    public function getPrefix(): string
    {
        return match ($this) {
            self::DEVIS => 'DEV',
            self::BON_COMMANDE => 'BDC',
            self::BON_LIVRAISON => 'BL',
            self::FACTURE => 'FAC',
            self::FACTURE_ACOMPTE => 'ACO',
            self::AVOIR => 'AVO',
        };
    }

    /**
     * Types qui peuvent être convertis vers ce type.
     */
    public function acceptsConversionFrom(): array
    {
        return match ($this) {
            self::BON_COMMANDE => [self::DEVIS],
            self::BON_LIVRAISON => [self::BON_COMMANDE],
            self::FACTURE => [self::BON_LIVRAISON, self::BON_COMMANDE, self::DEVIS],
            self::AVOIR => [self::FACTURE, self::FACTURE_ACOMPTE],
            default => [],
        };
    }
}
