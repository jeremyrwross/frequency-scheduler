<?php

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Container\Container;
use Jeremyrwross\FrequencyScheduler\FrequencyScheduler;
use Jeremyrwross\FrequencyScheduler\FrequencySchedulerServiceProvider;

beforeEach(function () {
    (new FrequencySchedulerServiceProvider(new Container))->boot();
});

afterEach(function () {
    Carbon::setTestNow();
    mt_srand();
});

it('checks the period when Laravel evaluates the event', function () {
    Carbon::setTestNow('2026-09-24 08:00:00');
    $event = new Event($this->createMock(EventMutex::class), 'example');
    $event->frequencyByPeriod('09:00', '17:00', 100);

    Carbon::setTestNow('2026-09-24 12:00:00');
    expect($event->filtersPass(new Container))->toBeTrue();

    Carbon::setTestNow('2026-09-24 18:00:00');
    expect($event->filtersPass(new Container))->toBeFalse();
});

it('draws a new probability for each evaluation', function () {
    Carbon::setTestNow('2026-09-24 12:00:00');
    $event = new Event($this->createMock(EventMutex::class), 'example');
    $event->frequencyByPeriod('09:00', '17:00', 50);

    mt_srand(123);
    $expected = array_map(fn () => mt_rand(1, 100) <= 50, range(1, 20));
    mt_srand(123);
    $actual = array_map(fn () => $event->filtersPass(new Container), range(1, 20));

    expect($actual)->toBe($expected);
});

it('honors the event timezone even when configured after the period', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-24 12:00:00', 'UTC'));
    $event = new Event($this->createMock(EventMutex::class), 'example');
    $event->frequencyByPeriod('08:00', '10:00', 100)->timezone('America/Halifax');

    expect($event->filtersPass(new Container))->toBeTrue();
});

it('rejects invalid clock times even at zero frequency', function ($start, $end, $frequency) {
    expect(fn () => FrequencyScheduler::frequencyByPeriod($start, $end, $frequency))
        ->toThrow(InvalidArgumentException::class);
})->with([
    ['25:00', '23:00', 100],
    ['12:00', '12:60', 100],
    ['99:99', '23:00', 0],
    ["09:00\n", '17:00', 100],
]);

it('handles overnight windows and inclusive boundaries', function ($time, $expected) {
    Carbon::setTestNow('2026-09-24 '.$time);
    expect(FrequencyScheduler::frequencyByPeriod('18:00', '06:00', 100))->toBe($expected);
})->with([
    ['17:59:00', false],
    ['18:00:00', true],
    ['23:59:00', true],
    ['00:00:00', true],
    ['06:00:59', true],
    ['06:01:00', false],
    ['12:00:00', false],
]);
