<?php

namespace App\Console\Commands;

use App\Models\Datapoint;
use App\Models\Metric;
use Illuminate\Console\Command;

class DataProcessCommand extends Command
{
    protected $signature = 'data:process';

    protected $description = 'Command description';

    public function handle(): void
    {
        $lastTime = time();
        sleep(1);

        $metrics = Metric::whereIn('name',[
            'hydrogen_sulfide',
            'dissolved_oxygen',
            'potential_of_hydrogen',
        ]);

        while(true) {
           $datapoints = Datapoint::where('created_at', '<', date("Y-m-d H:i:s", $lastTime))->get();
           foreach($datapoints as $datapoint) {

           }
        }
    }
}
