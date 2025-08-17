<?php

namespace App\Enums;

enum StateEnum: int
{
    case UNKNOWN = 0;
    case NORMAL = 1;
    case WARNING = 2;

    case ERROR = 3;
}
