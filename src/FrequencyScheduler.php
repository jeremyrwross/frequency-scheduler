<?php

namespace Jeremyrwross\FrequencyScheduler;

use Carbon\Carbon;
use DateTimeZone;
use InvalidArgumentException;

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
class FrequencyScheduler
{
    /**
     * Bootstrap any application services.
     *
     * @param  string  $startTime  The start time in 'H:i' format.
     * @param  string  $endTime  The end time in 'H:i' format.
     * @param  int  $frequencyPercentage  The frequency percentage (0-100).
     * @param  DateTimeZone|string|null  $timezone  The timezone for the period, or PHP's default.
     */
    public static function frequencyByPeriod(string $startTime, string $endTime, int $frequencyPercentage, DateTimeZone|string|null $timezone = null): bool
    {

        if (! preg_match('/\A([01][0-9]|2[0-3]):[0-5][0-9]\z/', $startTime) || ! preg_match('/\A([01][0-9]|2[0-3]):[0-5][0-9]\z/', $endTime)) {
            throw new InvalidArgumentException('Start time and end time must be in the format H:i.');
        }

        if (! is_int($frequencyPercentage) || $frequencyPercentage < 0 || $frequencyPercentage > 100) {
            throw new InvalidArgumentException('Frequency percentage must be an integer between 0 and 100.');
        }

        if ($frequencyPercentage === 0) {
            return false;
        }

        // Validated, zero-padded clock times can be compared directly.
        $currentTime = Carbon::now($timezone)->format('H:i');

        // Handle wrapping time ranges like "23:00 to 02:00"
        if ($endTime < $startTime) {
            if (! ($currentTime >= $startTime || $currentTime <= $endTime)) {
                return false;
            }
        } else {
            if ($currentTime < $startTime || $currentTime > $endTime) {
                return false;
            }
        }

        // Check if the frequency percentage condition is met
        return mt_rand(1, 100) <= $frequencyPercentage;

    }
}
