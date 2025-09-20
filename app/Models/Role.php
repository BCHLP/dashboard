<?php
declare(strict_types=1);

namespace App\Models;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $hidden = [
        'created_at',
        'updated_at',
        'guard_name',
    ];
}
