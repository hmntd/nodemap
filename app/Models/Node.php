<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagram_id',
        'type',
        'label',
        'position_x',
        'position_y',
        'metadata',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'metadata' => 'array',
    ];

    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class);
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'source_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'target_node_id');
    }
}
