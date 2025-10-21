<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionEnum: string
{
    case USERS = 'manage-users';
    case SERVERS = 'manage-servers';
}
