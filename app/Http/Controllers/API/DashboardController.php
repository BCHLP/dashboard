<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\SendCameraCaptureJob;

class DashboardController extends Controller
{
    public function captureImage()
    {
        SendCameraCaptureJob::dispatch();
    }
}
