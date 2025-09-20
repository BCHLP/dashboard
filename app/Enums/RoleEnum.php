<?php
declare(strict_types=1);

namespace App\Enums;

enum RoleEnum : string
{
    case ADMIN = 'Admin';
    case USER_MANAGEMENT = 'User Management';
    case SERVER_MANAGEMENT = 'Server Management';
}
