# Frequency Scheduler

Frequency Scheduler is a Laravel package that allows you to schedule tasks based on a frequency percentage within a specified time period.

## Compatibility

This version supports Laravel **12 and 13**, with PHP **8.3 or later within PHP 8**.
Laravel 12 applications running PHP 8.2 must upgrade PHP to use this package.

Composer enforces the supported Laravel majors through the required Illuminate
components (`^12.0 || ^13.0`). Future Laravel majors are added after compatibility
testing, rather than accepted automatically.

The GitHub Actions workflow runs the test suite against the latest compatible
dependencies for Laravel 12 and 13 on PHP 8.3, 8.4, and 8.5. Support targets Laravel
versions still receiving security fixes under
[Laravel's support policy](https://laravel.com/docs/13.x/releases#support-policy).
Dropping a supported Laravel major is a breaking package change and belongs in
a new major package release; existing releases keep their declared constraints.

## Installation

You can install the package via composer:
```bash
composer require jeremyrwross/frequency-scheduler:^2.0
```

## Usage

Each scheduled opportunity gets an independent chance to run. Set the interval
explicitly; Laravel otherwise checks the task every minute.

```php
// About six runs per hour during peak hours.
$schedule->command('social:post')
    ->everyFiveMinutes()
    ->frequencyByPeriod('09:00', '16:59', 50)
    ->timezone('America/Halifax')
    ->withoutOverlapping();

// About 1.2 runs per hour outside peak hours, including overnight.
$schedule->command('social:post')
    ->everyFiveMinutes()
    ->frequencyByPeriod('17:00', '08:59', 10)
    ->timezone('America/Halifax')
    ->withoutOverlapping();
```

The command names above are examples; use your application's command.

- Percentages describe the chance at each opportunity, not an exact quota of
  runs per period. Runs may be consecutive, and long gaps are possible. Other
  scheduler constraints and unavailable work can reduce actual posts.
- Use separate schedule entries for different periods. Chaining
  `frequencyByPeriod()` calls requires **all** periods to pass, so disjoint
  periods on the same entry will never run.
- Times use the 24-hour `H:i` format (`00:00` through `23:59`). Both endpoint
  minutes are included. Avoid overlapping windows: two separate entries can
  both run in an overlapping minute, even with `withoutOverlapping()` if the
  first command finishes before the second starts.
- An end time earlier than the start wraps across midnight. Equal endpoints
  mean a single minute; use `00:00` to `23:59` for the full day.
- The task's timezone is used, falling back to PHP's default timezone (normally
  Laravel's application timezone). Daylight-saving clock changes can skip or
  repeat local opportunities.
- `0` never runs; `100` always passes this filter inside the period.
- `withoutOverlapping()` prevents concurrent executions; it does not enforce
  a minimum gap between completed posts. This package does not persist posting
  history or retry missed opportunities.

For a custom condition, the helper is also available directly:

```php
FrequencyScheduler::frequencyByPeriod('09:00', '16:59', 50, 'America/Halifax');
```

Import `Jeremyrwross\FrequencyScheduler\FrequencyScheduler` when using the helper.

## Development

```bash
composer install
composer test
```

To check a specific supported Laravel major locally:

```bash
composer update --with 'laravel/framework:^12.0'
composer test
composer update --with 'laravel/framework:^13.0'
composer test
```

The package version is derived from Git release tags.

## Upgrading from 1.x

Version 2.0 requires Laravel 12 or 13 and PHP 8.3 or later within PHP 8.
Update your Composer constraint to `^2.0`. The three-argument scheduling macro
remains compatible, including overnight windows and inclusive endpoint minutes.

Period checks now run when Laravel evaluates the event and respect the event's
timezone. Invalid clock times such as `25:00` are rejected. Review any explicit
event timezones, since version 1 ignored them for the period check.
