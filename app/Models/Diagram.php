<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagram extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }

    public function edges(): HasMany
    {
        return $this->hasMany(Edge::class);
    }
}
