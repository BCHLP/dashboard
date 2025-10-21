<?php

namespace App\Enums;

enum TreatmentStageEnum: int
{
    case AVAILABLE = 0;
    case FILLING = 1;
    case PROCESSING = 2;
    case TRANSFERRING = 3;

}
