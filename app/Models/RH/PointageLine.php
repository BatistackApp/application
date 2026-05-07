<?php

namespace App\Models\RH;

use App\Enums\RH\Periode;
use App\Enums\RH\TypeHeure;
use App\Models\Chantier\Chantier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointageLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'pointage_session_id',
        'chantier_id',
        'date',
        'periode',
        'type_heure',
        'heures',
        'heures_trajet',
        'panier_repas',
        'grand_deplacement',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'periode' => Periode::class,
            'type_heure' => TypeHeure::class,
            'heures' => 'decimal:2',
            'heures_trajet' => 'decimal:2',
            'panier_repas' => 'boolean',
            'grand_deplacement' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PointageSession::class, 'pointage_session_id');
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    /**
     * Mutateur — grand_deplacement et panier_repas sont mutuellement exclusifs.
     */
    protected function grandDeplacement(): Attribute
    {
        return Attribute::make(
            set: function (bool $value) {
                if ($value) {
                    $this->attributes['panier_repas'] = false;
                }

                return $value;
            }
        );
    }

    protected function panierRepas(): Attribute
    {
        return Attribute::make(
            set: function (bool $value) {
                if ($value) {
                    $this->attributes['grand_deplacement'] = false;
                }

                return $value;
            }
        );
    }
}
