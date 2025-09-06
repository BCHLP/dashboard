<?php

namespace Database\Factories;

use App\Enums\TreatmentStageEnum;
use App\Models\Node;
use App\Models\TreatmentLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TreatmentLineFactory extends Factory
{
    protected $model = TreatmentLine::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomLetter(),
            'stage' => TreatmentStageEnum::AVAILABLE,
            'maintenance_mode' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
