<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TiersAddressType;
use App\Models\Core\CompanyInfo;
use App\Models\Tiers\Tiers;
use App\Services\Core\DocumentService;
use Illuminate\Support\Str;

class TiersDocumentGenerator extends DocumentService
{
    /**
     * Génère la fiche détaillée d'un tiers au format PDF.
     *
     * @param  Tiers  $tiers  Le modèle du tiers
     * @return string Le chemin du fichier généré
     */
    public function ficheTiers(Tiers $tiers): string
    {
        $data = [
            'tiers' => $tiers,
            'addresses' => $tiers->addresses()->get(),
            'contacts' => $tiers->contacts()->get(),
            'gestion' => $tiers->setting()->first(),
            'title' => 'Fiche Tiers',
        ];

        return $this->generate('pdf.tiers.fiche_tiers', $data, "fiche_tiers_{$tiers->code}", 'tiers');
    }

    /**
     * Génère une lettre personnalisée pour un tiers.
     *
     * @param  Tiers  $tiers  Le modèle du tiers
     * @param  string  $object  L'objet de la lettre (utilisé pour le nom du fichier)
     * @param  string  $content  Le contenu du corps de la lettre
     * @return string Le chemin du fichier généré
     */
    public function letter(Tiers $tiers, string $object, string $content): string
    {
        $data = [
            'tiers' => $tiers,
            'address' => $tiers->addresses()->where('address_type', TiersAddressType::INVOICING)->first(),
            'contact' => $tiers->contacts()->first(),
            'title' => $object,
            'content' => $content,
            'company' => CompanyInfo::first(),
        ];
        $slug = Str::slug($object, '_');

        return $this->generate('pdf.tiers.letter', $data, "{$slug}_{$tiers->code}", 'tiers');
    }

    /**
     * Génère la liste complète des tiers au format PDF.
     *
     * @return string Le chemin du fichier généré
     */
    public function listeTiers(): string
    {
        $data = [
            'tiers' => Tiers::all(),
            'title' => 'Liste des Tiers',
        ];

        return $this->generate('pdf.tiers.liste', $data, 'liste_tiers', 'tiers', 'landscape');
    }
}
