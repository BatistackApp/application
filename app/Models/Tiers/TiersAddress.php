<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TiersAddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

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

    protected function casts(): array
    {
        return [
            'address_type' => TiersAddressType::class,
        ];
    }

    public function getFullAddress(): HtmlString
    {
        return new HtmlString("{$this->address}<br>{$this->postal_code} {$this->city}<br>{$this->country}");
    }
}
