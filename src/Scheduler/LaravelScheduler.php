<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Scheduler;

use DateTimeZone;
use Egst\EmorfiqScheduler\Bridge\LaravelScheduler\LaravelMutex;
use Egst\EmorfiqScheduler\Job;
use Egst\EmorfiqScheduler\Scheduler;
use Egst\EmorfiqScheduler\SchedulerConfig;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\SchedulingMutex;
use Illuminate\Contracts\Foundation\Application;
use Override;

/**
 * Scheduler adapter for the Laravel scheduler library.
 *
 * - Set up a cron entry to execute the Laravel command as per Laravel documentation.
 * - Create a service provider in your Laravel application.
 * - Initialize this scheduler with the create method in ServiceProvider::register.
 * - You can pass this scheduler to your application
 *     in the ServiceProvider::boot method
 *     and use it via the library-agnostic Scheduler interface.
 *
 * See examples/laravel/ for a demonstration of how this can be set up.
 */
final readonly class LaravelScheduler implements Scheduler {

    public function __construct (
        private readonly Schedule     $schedule,
        private readonly DateTimeZone $timeZone,
    ) {
    }

    public static function create (Application $app, SchedulerConfig $config): self {
        // This injects the selected locking backend from the given lock provider
        // for Laravel to use in its internal scheduler locking mechanism.
        // Injecting the EventMutex is simpler than using the intended Schedule::useCache method.
        if ($config->sharedLocking !== null) {
            $app->singleton(EventMutex::class, fn () => new LaravelMutex($config->sharedLocking));
            $app->singleton(SchedulingMutex::class, fn () => new LaravelMutex($config->sharedLocking));
        }

        // Not injecting the Schedule into the DI container.
        // That's up to the application to decide.
        return new self($app->make(Schedule::class), $config->timeZone);
    }

    #[Override]
    public function addJob (Job $job): self {
        $event = $this->schedule
            ->call($job->handler)
            ->name($job->name)
            ->cron((string) $job->cronExpression)
            ->timezone($this->timeZone);

        if ($job->overlapLock)
            $event->withoutOverlapping();

        if ($job->minuteLock)
            $event->onOneServer();

        if ($job->description !== null)
            $event->description($job->description);

        return $this;
    }

}
