<?php

namespace App\Console\Commands;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\MetricBaseline;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BaselineCommand extends Command
{
    protected $signature = 'baseline';

    protected $description = 'Update the baseline based off Datapoints';

    public function handle(): void
    {
        $service = app(\App\Services\BaselineService::class);
        $service();
    }
}
