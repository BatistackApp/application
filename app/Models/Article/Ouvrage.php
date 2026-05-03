<?php

namespace App\Models\Article;

use App\Enums\UnitOfMesure;
use App\Observers\Article\OuvrageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([OuvrageObserver::class])]
class Ouvrage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit',
        'is_active',
        'article_id',
        'cump_ht',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'ouvrage_article')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit' => UnitOfMesure::class,
        ];
    }
}
