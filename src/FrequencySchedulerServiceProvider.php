<?php

namespace Jeremyrwross\FrequencyScheduler;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class FrequencySchedulerServiceProvider
 *
 * This service provider adds a macro to the Event class to schedule tasks
 * based on a frequency percentage within a specified time period.
 *
 * @category Scheduling
 *
 * @author  Jeremy Ross <jeremyrwross@gmail.com>
 * @license https://opensource.org/licenses/MIT MIT License
 *
 * @link https://github.com/jeremyrwross/frequency-scheduler
 */
class FrequencySchedulerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Event::macro(
            'frequencyByPeriod', function ($startTime, $endTime, $frequencyPercentage) {
                return $this->when(FrequencyScheduler::frequencyByPeriod($startTime, $endTime, $frequencyPercentage));
            }
        );
    }
}
