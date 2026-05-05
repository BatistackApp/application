<?php

namespace App\Models\Chantier;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ChantierDocument extends Model
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'chantier_id',
        'user_id',
        'nom',
        'type',
        'description',
    ];

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('chantier_documents')
            ->useDisk('public');
    }
}
