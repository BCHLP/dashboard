<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodeSetting extends Model
{
    use HasFactory;

    protected $fillable = ['node_id', 'name', 'value', 'cast'];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function value() : string|bool|int|float {
        switch($this->cast) {
            case 'boolean':
            case 'bool':
                return $this->value === 'true';
            case 'int':
            case 'integer':
                return (int) $this->value;
            case 'float':
            case 'double':
                return (float) $this->value;
        }
        return $this->value;
    }
}
