<?php

namespace App\Models\Tiers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiersContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiers_id',
        'first_name',
        'last_name',
        'fonction',
        'tel_fix',
        'tel_portable',
        'email',
        'dgcp_concilent',
    ];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    protected function casts(): array
    {
        return [
            'dgcp_concilent' => 'boolean',
        ];
    }
}
