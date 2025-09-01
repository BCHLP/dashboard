<?php

namespace App\Console\Commands;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Sensor;
use Illuminate\Console\Command;

class DataFakeCommand extends Command
{
    protected $signature = 'data:fake';

    protected $description = 'Command description';

    public function handle(): void
    {

        $metrics = Metric::get();

        $this->info("Starting loop with {$metrics->count()} metrics");
        while(true) {

            foreach($metrics as $metric) {
                foreach($metric->devices as $device) {
                    $datapoint = Datapoint::create([
                        'device_metric_id' => $device->id,
                        'metric_id' => $metric->id,
                        'value' => $this->getRandomValue($metric)
                    ]);
                    // $this->line("Created datapoint for sensor {$sensor->name} for metric {$metric->name}");
                    $this->line(print_r($datapoint, true));
                }
            }


            sleep(1);
        }
    }

    private function getRandomValue(Metric $metric) : float {
        $min = floatval($metric->baseline_minimum);
        $max = floatval($metric->baseline_maximum);
        return $min + ($max - $min) * (mt_rand() / mt_getrandmax());
    }
}
