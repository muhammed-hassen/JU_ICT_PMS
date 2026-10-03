<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = ['subject', 'created_by', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** Conversations this user takes part in. */
    public function scopeFor(Builder $query, User $user): Builder
    {
        return $query->whereHas('participants', fn ($q) => $q->where('users.id', $user->id));
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants->contains('id', $user->id);
    }

    /** The subject, or the other people's names when there is none. */
    public function titleFor(User $user): string
    {
        if ($this->subject) {
            return $this->subject;
        }

        $others = $this->participants->where('id', '!=', $user->id)->pluck('name');

        return $others->isEmpty() ? 'Just you' : $others->join(', ', ' and ');
    }

    /** Messages from other people newer than this user's last visit. */
    public function unreadCountFor(User $user): int
    {
        $lastRead = $this->participants->firstWhere('id', $user->id)?->pivot->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->when($lastRead, fn ($q) => $q->where('created_at', '>', $lastRead))
            ->count();
    }

    public function markReadFor(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);
    }
}
