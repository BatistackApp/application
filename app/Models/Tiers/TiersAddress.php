<?php

namespace App\Models\Tiers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiersAddress extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tiers_id',
        'address_type',
        'address',
        'postal_code',
        'city',
        'country',
        'address_name',
        'phone',
        'email',
    ];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }
}
