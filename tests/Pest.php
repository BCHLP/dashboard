<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Enums\NodeTypeEnum;
use App\Models\Metric;
use App\Models\Node;
use App\Models\TreatmentLine;

pest()->extend(Tests\TestCase::class)->beforeEach(function () {
    Metric::create(['name' => 'Flow Rate', 'alias' => 'fr']);
    Metric::create(['name' => 'Water Level', 'alias' => 'wl']);
})
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createTank(TreatmentLine $line, string $name='TANK', ?Node $parent=null) : Node  {

    if ($parent) {
        $node = $parent->children()->create([
            'name' => $name,
            'node_type' => NodeTypeEnum::SEDIMENTATION_TANK,
            'treatment_line_id' => $line->id,
        ]);
    } else {
        $node = Node::factory(['name' => $name, 'node_type' => NodeTypeEnum::SEDIMENTATION_TANK])->create();
    }
    return $node;
}

function createValve(TreatmentLine $line, string $name='VALVE', ?Node $parent=null) : Node  {

    if ($parent) {
        $node = $parent->children()->create([
            'name' => 'VALVE ',
            'node_type' => NodeTypeEnum::VALVE,
            'treatment_line_id' => $line->id,
        ]);
    } else {
        $node = Node::factory(['name' => $name, 'node_type' => NodeTypeEnum::VALVE])->create();
    }
    return $node;
}

function createLine(array $attributes = []) {
    return TreatmentLine::factory($attributes)->create();
}
