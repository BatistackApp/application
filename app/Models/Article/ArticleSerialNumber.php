<?php

namespace App\Models\Article;

use App\Enums\Article\SerialNumberStatus;
use App\Models\Core\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;

class ArticleSerialNumber extends Model
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'article_id',
        'warehouse_id',
        'serial_number',
        'status',
        'assigned_user_id',
        'photo_plate_path',
        'purchase_date',
        'warranty_expiry',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'status' => SerialNumberStatus::class,
        ];
    }
}
