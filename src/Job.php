<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

use Closure;

/**
 * A simple job configuration object.
 *
 * This models the domain more clearly than a complicated fluent builder interface.
 * If this keeps growing, a dedicated job builder can be added to mimic the more typical scheduler interface.
 *
 * Other important parameters to consider:
 * - force parallel execution: Crunz executes in parallel always, but in Laravel
 *     we'd have to create commands from the given closures to achieve that.
 * - custom locking: To avoid the limitations of Laravel we could add custom locking mechanism
 *     executed in the closure of the job. It would also allow us to pass the lock handle to
 *     the job handler so that long running tasks can refresh the lock or perform checks of its validity.
 * - custom TTL for overlap locks: This can be set via a parameter for Laravel
 *      or somehow injected into the locking backend for Crunz.
 */
final readonly class Job {

    /**
     * @param Closure (): void $handler
     * @param string           $name        Unique name of the job.
     * @param bool             $overlapLock Prevent overlapping execution of this job.
     *                                      If the job is still running during the next time slot,
     *                                      this lock will make sure to skip the next execution.
     * @param bool             $minuteLock  Prevent multiple executions of this job within the same time slot (one minute).
     *                                      If the scheduler is executed on multiple servers,
     *                                      this lock will make sure to only execute the job on one server
     *                                      even if the overlap lock is already released in that time slot.
     */
    public function __construct (
        public string         $name,
        public CronExpression $cronExpression,
        public Closure        $handler,
        public ?string        $description = null,
        public bool           $overlapLock = false,
        public bool           $minuteLock  = false,
    ) {
    }

}
