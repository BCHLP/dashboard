<?php

namespace App\Enums;

enum TreatmentStageEnum: int
{
    case AVAILABLE = 0;
    case TANK1_FILLING = 1;
    case TANK1_PROCESSING = 2;
    case TANK1_TRANSFER_TANK2= 3;

    case TANK2_PROCESSING = 4;
    case TANK2_TRANSFER_TANK3 = 5;

    case TANK3_PROCESSING = 6;
    case TANK3_EMPTYING = 7;


}
