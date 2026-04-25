<?php

namespace App\Models\Tiers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiersSettings extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tiers_id',
        'outstanding',
        'followup',
        'followup_terms',
    ];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    protected function casts(): array
    {
        return [
            'followup' => 'boolean',
            'followup_terms' => 'array',
        ];
    }
}
