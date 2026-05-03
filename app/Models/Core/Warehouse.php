<?php

namespace App\Models\Core;

use App\Models\Article\Article;
use App\Models\User;
use App\Observers\Core\WarehouseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([WarehouseObserver::class])]
class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'responsible_user_id',
        'name',
        'location',
        'latitude',
        'longitude',
        'is_active',
    ];

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_warehouse')
            ->withPivot('min_stock', 'max_stock', 'alert_stock', 'actual_stock', 'bin_location')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
