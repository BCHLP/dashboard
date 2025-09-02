<?php

namespace App\Enums;

enum NodeTypeEnum: int
{
    case SENSOR = 1;
    case SERVER = 2;
    case ROUTER = 3;
}
