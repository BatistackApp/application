<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TypeAccount;
use Database\Factories\UserFactory;
use FilamentInbox\Concerns\HasInbox;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\Sources\RelatedModelSource;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['name', 'email', 'password', 'type_account'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements HasTimeline
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasInbox, InteractsWithTimeline, LogsActivity, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type_account' => TypeAccount::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)
            ->fromActivityLog()
            ->fromActivityLogOf(['emails', 'notes', 'tasks'])
            ->fromRelation('emails', function (RelatedModelSource $source): void {
                $source
                    ->event(
                        column: 'sent_at',
                        event: 'email_sent',
                        icon: 'heroicon-o-paper-airplane',
                        color: 'primary',
                    )
                    ->event(
                        column: 'received_at',
                        event: 'email_received',
                        icon: 'heroicon-o-inbox-arrow-down',
                        color: 'info',
                    )
                    ->title(fn ($email): string => $email->subject ?? 'Email')
                    ->causer(fn ($email) => $email->from->first());
            });
    }
}
