<?php declare(strict_types = 1);

namespace App\Integration\Foo;

use Egst\EmorfiqScheduler\CronExpression;
use Egst\EmorfiqScheduler\Job;
use Predis\Client as PredisClient;

/**
 * Application demo.
 */
class FooSync {
    public function __construct (
        private PredisClient $redisClient,
    ) {
    }

    public function sync (): void {
        # ...
    }

    /**
     * The simple Job configuration interface allows flexible job
     * definitions that can be integrated into the applcation's
     * architecture in any way desired. You can use attributes and
     * class discovery or any other fancy system. You only need to
     * provide the Job definition.
     */
    public function getSchedulerTask (): Job {
        return new Job(
            'integration-foo-sync',
            new CronExpression('0 * * * *'), // Every hour
            $this->sync(...),
            description: 'Syncs data with the Foo service.',
            overlapLock: true,
            minuteLock: true,
        );
    }
}
