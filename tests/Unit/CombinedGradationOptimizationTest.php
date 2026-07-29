<?php

namespace Tests\Unit;

use App\Http\Controllers\MixDesign2012Controller;
use App\Models\AggregateTestRun;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CombinedGradationOptimizationTest extends TestCase
{
    public function test_it_finds_fine_and_coarse_percentages_with_the_smallest_deviation(): void
    {
        $fine = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r475'=>0,'r236'=>0,'r118'=>20.12,'r060'=>78.24,'r030'=>98.95,'r015'=>100],
        ]]]]);
        $coarse = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r375'=>0,'r190'=>0,'r095'=>85.76,'r475'=>100],
        ]]]]);

        $method = new ReflectionMethod(MixDesign2012Controller::class, 'optimizeCombinedGradation');
        $result = $method->invoke(new MixDesign2012Controller(), $fine, $coarse, 20, 4);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(100, $result['fine_percent'] + $result['coarse_percent'], 0.001);
        $this->assertGreaterThan(0, $result['fine_percent']);
        $this->assertLessThan(100, $result['fine_percent']);
        $this->assertGreaterThanOrEqual(0, $result['deviation']);
        $this->assertArrayHasKey('r475', $result['rows']);
    }

    public function test_it_searches_in_point_one_percent_steps_and_supports_average_curve(): void
    {
        $fine = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r475'=>0,'r236'=>0,'r118'=>20.12,'r060'=>78.24,'r030'=>98.95,'r015'=>100],
        ]]]]);
        $coarse = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r375'=>0,'r190'=>0,'r095'=>85.76,'r475'=>100],
        ]]]]);

        $method = new ReflectionMethod(MixDesign2012Controller::class, 'optimizeCombinedGradation');
        $result = $method->invoke(new MixDesign2012Controller(), $fine, $coarse, 20, 5);

        $this->assertNotNull($result);
        $this->assertSame(5, $result['curve']);
        $this->assertEqualsWithDelta(0, fmod($result['fine_percent'] * 10, 1), 0.000001);
        $this->assertEqualsWithDelta(38.75, $result['rows']['r475']['target'], 0.000001);
        $this->assertEqualsWithDelta(
            $result['rows']['r475']['combined'],
            $result['rows']['r475']['fine_weighted'] + $result['rows']['r475']['coarse_weighted'],
            0.000001
        );
        $this->assertSame('r190', array_key_first($result['rows']));
        $this->assertArrayNotHasKey('r375', $result['rows']);
        $this->assertGreaterThanOrEqual(0, $result['deviation']);
    }

    public function test_active_sieves_start_at_selected_maximum_aggregate_size(): void
    {
        $fine = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r475'=>0,'r236'=>0,'r118'=>20.12,'r060'=>78.24,'r030'=>98.95,'r015'=>100],
        ]]]]);
        $coarse = new AggregateTestRun(['results' => ['observations' => [[
            'sieve_cumulative' => ['r375'=>0,'r190'=>0,'r095'=>85.76,'r475'=>100],
        ]]]]);
        $method = new ReflectionMethod(MixDesign2012Controller::class, 'optimizeCombinedGradation');
        $controller = new MixDesign2012Controller();

        $size10 = $method->invoke($controller, $fine, $coarse, 10, 5);
        $size20 = $method->invoke($controller, $fine, $coarse, 20, 5);
        $size40 = $method->invoke($controller, $fine, $coarse, 40, 5);

        $this->assertSame('r095', array_key_first($size10['rows']));
        $this->assertSame('r190', array_key_first($size20['rows']));
        $this->assertSame('r375', array_key_first($size40['rows']));
        $this->assertCount(7, $size10['rows']);
        $this->assertCount(8, $size20['rows']);
        $this->assertCount(9, $size40['rows']);
    }
}
