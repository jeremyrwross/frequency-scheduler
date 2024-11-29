<?php

use Carbon\Carbon;
use Jeremyrwross\FrequencyScheduler\FrequencyScheduler;

it(
    'throws an exception if start time is not in H:i format', function () {
        FrequencyScheduler::frequencyByPeriod(123, '23:00', 50);
    }
)->throws(
    InvalidArgumentException::class,
    'Start time and end time must be in the format H:i.'
);

it(
    'throws an exception if end time is not in H:i format', function () {
        FrequencyScheduler::frequencyByPeriod('12:00', 123, 50);
    }
)->throws(
    InvalidArgumentException::class,
    'Start time and end time must be in the format H:i.'
);

it(
    'throws exception for invalid time format', function () {
        $this->expectException(InvalidArgumentException::class);
        FrequencyScheduler::frequencyByPeriod('invalid', '14:00', 50);
    }
);

it(
    'throws an exception if frequency percentage is less than 0', function () {
        FrequencyScheduler::frequencyByPeriod('22:00', '23:00', -1);
    }
)->throws(
    InvalidArgumentException::class,
    'Frequency percentage must be an integer between 0 and 100.'
);

it(
    'throws an exception if frequency percentage is greater than 100', function () {
        FrequencyScheduler::frequencyByPeriod('22:00', '23:00', 101);
    }
)->throws(
    InvalidArgumentException::class,
    'Frequency percentage must be an integer between 0 and 100.'
);

it(
    'returns false when frequency percentage is 0', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('12:00'));
        expect(FrequencyScheduler::frequencyByPeriod('10:00', '14:00', 0))->toBeFalse();
    }
);

it(
    'returns true when current time is exactly at start time', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('10:00'));
        expect(FrequencyScheduler::frequencyByPeriod('10:00', '14:00', 100))->toBeTrue();
    }
);

it(
    'returns true when current time is exactly at end time', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('14:00'));
        expect(FrequencyScheduler::frequencyByPeriod('10:00', '14:00', 100))->toBeTrue();
    }
);

it(
    'returns false when current time is outside the range', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('21:00'));
        expect(FrequencyScheduler::frequencyByPeriod('22:00', '23:00', 50))->toBeFalse();
        Carbon::setTestNow(); // Reset the time
    }
);

it(
    'returns true when current time is within range and random number is within frequency percentage', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('22:30'));
        expect(FrequencyScheduler::frequencyByPeriod('22:00', '23:00', 100))->toBeTrue();
        Carbon::setTestNow(); // Reset the time
    }
);

it(
    'returns false when current time is within range but random number is outside frequency percentage', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('22:30'));
        mt_srand(25); // Set random seed to ensure mt_rand() returns a value > 50
        expect(FrequencyScheduler::frequencyByPeriod('22:00', '23:00', 50))->toBeFalse();
        mt_srand(); // Reset the random seed
        Carbon::setTestNow(); // Reset the time
    }
);

it(
    'returns true when current time is within wrapped range and frequency percentage is 100', function () {
        Carbon::setTestNow(Carbon::createFromTimeString('01:00'));
        expect(FrequencyScheduler::frequencyByPeriod('18:00', '03:00', 100))->toBeTrue();
        Carbon::setTestNow(); // Reset the time
    }
);
