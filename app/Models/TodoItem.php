<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'todo_list_id',
        'title',
        'description',
        'due_date',
        'priority',
        'completed_at',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }
}
