<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum DocumentStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REFUSED = 'refused';
    case DELIVERED = 'delivered';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case APPLIED = 'applied';
    case CANCELLED = 'cancelled';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::SENT => 'Envoyé',
            self::ACCEPTED => 'Accepté',
            self::REFUSED => 'Refusé',
            self::DELIVERED => 'Livré',
            self::PARTIALLY_PAID => 'Partiellement payé',
            self::PAID => 'Payé',
            self::APPLIED => 'Appliqué',
            self::CANCELLED => 'Annulé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'info',
            self::ACCEPTED, self::PAID, self::APPLIED => 'success',
            self::REFUSED, self::CANCELLED => 'danger',
            self::DELIVERED, self::PARTIALLY_PAID => 'warning',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => Phosphor::FileDashed,
            self::SENT => Phosphor::PaperPlane,
            self::ACCEPTED, self::APPLIED, self::PAID => Phosphor::CheckCircle,
            self::REFUSED => Phosphor::XCircle,
            self::DELIVERED => Phosphor::Package,
            self::PARTIALLY_PAID => Phosphor::CurrencyEur,
            self::CANCELLED => Phosphor::Prohibit,
        };
    }

    /**
     * Statuts autorisés pour chaque type de document.
     */
    public static function forDocumentType(DocumentType $type): array
    {
        return match ($type) {
            DocumentType::DEVIS => [
                self::DRAFT,
                self::SENT,
                self::ACCEPTED,
                self::REFUSED,
                self::CANCELLED,
            ],
            DocumentType::BON_COMMANDE => [
                self::DRAFT,
                self::SENT,
                self::ACCEPTED,
                self::CANCELLED,
            ],
            DocumentType::BON_LIVRAISON => [
                self::DRAFT,
                self::SENT,
                self::DELIVERED,
                self::CANCELLED,
            ],
            DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE => [
                self::DRAFT,
                self::SENT,
                self::PARTIALLY_PAID,
                self::PAID,
                self::CANCELLED,
            ],
            DocumentType::AVOIR => [
                self::DRAFT,
                self::SENT,
                self::APPLIED,
            ],
        };
    }
}
