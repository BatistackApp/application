<?php

namespace App\Models\Chantier;

use App\Enums\Chantier\ChantierCoutType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChantierCout extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'user_id',
        'type',
        'designation',
        'montant_ht',
        'date_imputation',
        'source_type',
        'source_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChantierCoutType::class,
            'montant_ht' => 'decimal:2',
            'date_imputation' => 'date',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
