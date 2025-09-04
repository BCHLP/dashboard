<?php

namespace App\Enums;

enum NodeTypeEnum: int
{
    case SENSOR = 1;
    case SERVER = 2;
    case ROUTER = 3;

    case VALVE = 4;
    case PUMP = 5;
    case SCREEN = 6;
    case SEDIMENTATION_TANK = 7;
    case AERATION_TANK = 8;
    case OXYGENATOR = 9;

    case DIGESTION_TANK = 10; // used to break down sludge

    case INLET = 11;

    case OUTLET = 12;

    case INVISIBLE = 13;


}
