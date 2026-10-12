<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'priority', 'is_completed'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public const array PRIORITIES = ['low', 'medium', 'high'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_completed' => 'boolean'];
    }
}
