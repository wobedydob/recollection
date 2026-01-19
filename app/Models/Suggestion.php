<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }
}
