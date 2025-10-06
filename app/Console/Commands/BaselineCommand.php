<?php

namespace App\Console\Commands;

use App\Services\BaselineService;
use Illuminate\Console\Command;

class BaselineCommand extends Command
{
    protected $signature = 'baseline';

    protected $description = 'Update the baseline based off Datapoints';

    public function handle(): void
    {
        $service = new BaselineService();
        $service->createLoginAuditDatapoints()
            ->execute();
    }
}
