<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manhole extends Model
{
    use HasFactory;

    protected $fillable = ['id','sap_id', 'name'];

    protected $casts = [
        'coordinates' => Point::class,
    ];
}
