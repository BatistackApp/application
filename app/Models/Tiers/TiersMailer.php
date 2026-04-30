<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TiersMailerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiersMailer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiers_id',
        'subject',
        'content',
        'status',
        'published_at',
    ];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'timestamp',
            'status' => TiersMailerStatus::class,
        ];
    }
}
