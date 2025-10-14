<?php

namespace App\Services;

use App\Enums\MetricAliasEnum;
use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\MetricBaseline;
use App\Models\Node;
use App\Models\User;
use App\Models\UserLoginAudit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaselineService
{
    private Carbon $startDate;
    private Carbon $endDate;

    private bool $quietly = false;

    public function __construct() {
        $this->startDate = Carbon::now()->subHours(2)->setMinutes(0)->setSeconds(0);
        $this->endDate = Carbon::now()->subHours()->setMinutes(0)->setSeconds(0);
    }

    public function quietly() : self {
        $this->quietly = true;
        return $this;
    }

    public function createLoginAuditDatapoints() : self {

        $failedMetric = Metric::where('alias', MetricAliasEnum::USER_AUTH_FAILED)->first();
        $successfulmetric = Metric::where('alias', MetricAliasEnum::USER_AUTH_SUCCESSFUL)->first();

        $users = User::all();
        foreach ($users as $user) {

            $counts = UserLoginAudit::select('successful', DB::raw('COUNT(*) as counts'))
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('user_id', $user->id)
                ->groupBy('successful')->get();

            $successful = $counts->where('successful',true)->first()->counts ?? 0;
            $failed = $counts->where('successful',false)->first()->counts ?? 0;

            if ($this->quietly) {
                Datapoint::createQuietly([
                    'source_id' => $user->id,
                    'source_type' => User::class,
                    'metric_id' => $failedMetric->id,
                    'time' => $this->startDate->timestamp,
                    'value' => $failed]);

                Datapoint::createQuietly([
                    'source_id' => $user->id,
                    'source_type' => User::class,
                    'metric_id' => $successfulmetric->id,
                    'time' => $this->startDate->timestamp,
                    'value' => $successful]);
                continue;
            }

            Datapoint::create([
                'source_id' => $user->id,
                'source_type' => User::class,
                'metric_id' => $failedMetric->id,
                'time' => $this->startDate->timestamp,
                'value' => $failed]);

            Datapoint::create([
                'source_id' => $user->id,
                'source_type' => User::class,
                'metric_id' => $successfulmetric->id,
                'time' => $this->startDate->timestamp,
                'value' => $successful]);
        }

        return $this;
    }

    public function execute() : void
    {
        $metrics = Metric::all();
        $users = User::all();

        foreach ($metrics as $metric) {

            if ($metric->alias === MetricAliasEnum::USER_AUTH_FAILED->value
                || $metric->alias === MetricAliasEnum::USER_AUTH_SUCCESSFUL->value) {
                foreach($users as $user) {

                    $this->create($metric, $user, User::class);
                }
                continue;

            }


            foreach($metric->nodes as $node) {
                $this->create($metric, $node, Node::class);
            }
        }
    }

    private function create(Metric $metric, Node|User $source, string $sourceClass) : MetricBaseline {

        $datapoints = Datapoint::where('metric_id', $metric->id)
            ->where('source_id', $source->id)
            ->where('source_type', $sourceClass)
            ->whereBetween('time', [$this->startDate->timestamp, $this->endDate->timestamp])
            ->pluck('value');

        return MetricBaseline::updateOrCreate([
            'metric_id' => $metric->id,
            'source_id' => $source->id,
            'source_type' => $sourceClass,
            'dow' => $this->startDate->dayOfWeek(),
            'hour' => $this->startDate->hour,
        ],[
            'mean' => $datapoints->avg() ?? 0,
            'median' => $datapoints->median() ?? 0,
            'sd' => $datapoints->sd() ?? 0,
        ]);
    }
}
