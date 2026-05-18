<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Scheduler;

use Crunz\Schedule;
use DateTimeZone;
use Egst\EmorfiqScheduler\Bridge\SymfonyLock\SymfonyStore;
use Egst\EmorfiqScheduler\Exception\FeatureNotSupported;
use Egst\EmorfiqScheduler\Job;
use Egst\EmorfiqScheduler\Scheduler;
use Egst\EmorfiqScheduler\SchedulerConfig;
use Override;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * Scheduler adapter for the Crunz scheduler library.
 *
 * - Set up a cron entry to execute the Crunz command as per Crunz documentation
 *     and give it a directory for the task definition files.
 * - Initialize this scheduler with the create method in one of those
 *     task definition files.
 * - You can pass this scheduler to your application
 *     and use it via the library-agnostic Scheduler interface.
 * - Make sure to return the underlying Crunz schedule (getSchedule)
 *     from the definition file.
 *
 * See examples/crunz/ for a demonstration of how this can be set up.
 */
final readonly class CrunzScheduler implements Scheduler {
    public function __construct (
        private Schedule                  $schedule,
        private DateTimeZone              $timeZone,
        private ?PersistingStoreInterface $store,
    ) {}

    /**
     * @param Schedule $schedule  An existing Crunz Schedule can be passed here to extend an existing schedule.
     * @param float    $lockDelay See SymfonyStore for the explanation.
     *                            This is just a small implementation detail for completeness
     *                            but probably not necessary for most use-cases.
     */
    public static function create (
        SchedulerConfig $config,
        Schedule        $schedule  = new Schedule,
        ?float          $lockDelay = null,
    ): self {
        return new self(
            schedule: $schedule,
            timeZone: $config->timeZone,
            store: $lockDelay
                ? new SymfonyStore($config->ownedLocking, $lockDelay)
                : new SymfonyStore($config->ownedLocking)
        );
    }

    #[Override]
    public function addJob (Job $job): self {
        $event = $this->schedule
            ->run($job->handler);

        if ($job->overlapLock) {
            $event->preventOverlapping($this->store);
        }

        if ($job->minuteLock) {
            throw new FeatureNotSupported('Distributed locking is not supported by the Crunz scheduler.');
            # TODO: This could be implemented with the skip method.
        }

        $event
            ->name($job->name)
            ->description($job->description)
            ->cron((string) $job->cronExpression)
            ->timezone($this->timeZone);

        return $this;
    }

    public function getSchedule (): Schedule {
        return $this->schedule;
    }
}
