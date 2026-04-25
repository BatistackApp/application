<?php

namespace App\Models\Tiers;

use App\Enums\Civility;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersStatus;
use App\Enums\Tiers\TiersTypology;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tiers extends Model
{
    use HasFactory;

    protected $fillable = [
        'civility',
        'name',
        'typology',
        'category',
        'siren',
        'naf',
        'num_tva',
        'code',
        'dgpd_concilient',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'dgpd_concilient' => 'boolean',
            'civility' => Civility::class,
            'typology' => TiersTypology::class,
            'category' => TiersCategory::class,
            'status' => TiersStatus::class,
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(TiersAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(TiersContact::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(TiersSettings::class);
    }
}
