<?php declare(strict_types = 1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\SchedulerSetup;
use Egst\EmorfiqScheduler\Scheduler;
use Egst\EmorfiqScheduler\Scheduler\LaravelScheduler;

/**
 * Laravel scheduler adapter setup boilerplate.
 *
 * The Laravel scheduler requires injecting the locking backend into the DI
 * container. Initialize the Laravel scheduler adapter in a service provider
 * register method and call your application code as needed to provide the
 * configuration. Execute the job definitions in the boot method. This ensures the
 * correct initialization order. The Scheduler interface and the SchedulerConfig
 * class provide library-agnostic interface to use in your application without any
 * library-specific dependencies.
 */
class SchedulerServiceProvider extends ServiceProvider {
    public function register (): void {
        $config    = $this->app->make(SchedulerSetup::class)->getConfig();
        $scheduler = LaravelScheduler::create($this->app, $config);
        $this->app->register(Scheduler::class, fn () => $scheduler);
    }

    public function boot (): void {
        $scheduler = $this->app->make(Scheduler::class);
        $this->app->make(SchedulerSetup::class)->define($scheduler);
    }
}
