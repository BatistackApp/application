<?php

namespace App\Models\Tiers;

use App\Enums\Civility;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersStatus;
use App\Enums\Tiers\TiersTypology;
use App\Observers\Tiers\TiersObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[ObservedBy([TiersObserver::class])]
class Tiers extends Model implements HasTimeline
{
    use HasFactory, InteractsWithTimeline, LogsActivity;

    protected $fillable = [
        'civility',
        'name',
        'typology',
        'category',
        'siren',
        'naf',
        'num_tva',
        'code',
        'dgpd_concilient',
        'website',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'dgpd_concilient' => 'boolean',
            'civility' => Civility::class,
            'typology' => TiersTypology::class,
            'category' => TiersCategory::class,
            'status' => TiersStatus::class,
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(TiersAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(TiersContact::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(TiersSettings::class);
    }

    public function mailers(): HasMany
    {
        return $this->hasMany(TiersMailer::class);
    }

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)
            ->fromActivityLog()
            ->fromActivityLogOf(['addresses', 'contacts']);
    }
}
