<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description', 'priority', 'is_completed', 'user_id'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public const array PRIORITIES = ['low', 'medium', 'high'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->is_admin) {
            $query->where('user_id', $user->id);
        }
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_completed' => 'boolean', 'user_id' => 'integer'];
    }
}
