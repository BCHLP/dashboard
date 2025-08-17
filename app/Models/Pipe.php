<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\LineString;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipe extends Model
{
    use HasFactory;

    protected $fillable = ['id','name','path'];

    protected $casts = [
        'path' => LineString::class
    ];
}
