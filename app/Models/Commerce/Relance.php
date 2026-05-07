<?php

namespace App\Models\Commerce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relance extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'user_id',
        'date_relance',
        'type',
        'contenu',
        'reponse_client',
    ];

    protected function casts(): array
    {
        return [
            'date_relance' => 'date',
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'facture_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
