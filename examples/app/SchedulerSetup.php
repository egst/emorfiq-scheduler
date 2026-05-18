<?php declare(strict_types = 1);

namespace App;

use App\Integration\Foo\FooSync;
use App\Reporting\ReportGenerator;
use DateTimeZone;
use Egst\EmorfiqScheduler\CronExpression;
use Egst\EmorfiqScheduler\Scheduler;
use Egst\EmorfiqScheduler\SchedulerConfig;
use Egst\EmorfiqScheduler\Job;
use Egst\EmorfiqScheduler\Locking\Store\PdoStore;
use Egst\EmorfiqScheduler\Locking\SymfonyLock\SymfonyLockProvider;
use Illuminate\Contracts\Config\Repository as Config;
use PDO;
use Predis\Client as PredisClient;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * This is a simple example of how you can set up the scheduler and its jobs
 * without any library-specific code in your application. Only the initialization
 * of the scheduler needs to be taken care of separately. Then you can pass it
 * your library-agnostic application code.
 */
final class SchedulerSetup {

    public function __construct(
        private Config $config,
        private PredisClient $redisClient,
        private ReportGenerator $reportGenerator,
        private FooSync $fooSync,
        private PDO $pdo,
    ) {}

    public function getConfig (): SchedulerConfig {
        return new SchedulerConfig(
            timeZone:      new DateTimeZone($this->config->get('app.timezone')),
            sharedLocking: new PdoStore($this->pdo),
            ownedLocking:  new SymfonyLockProvider(new RedisStore($this->redisClient)),
        );
    }

    public function define (Scheduler $scheduler): void {
        $scheduler
            ->addJob(new Job(
                'hello-world',
                new CronExpression('0 * * * *'),
                static function () { print('Hello world!' . PHP_EOL); },
            ))
            ->addJob(new Job(
                'backup',
                new CronExpression('0 * * * *'),
                static function () { exec('cp -r data/ data_backup/'); },
            ))
            ->addJob(new Job(
                'generate-report',
                new CronExpression('0 * * * *'),
                fn () => $this->reportGenerator->sendReport(),
                overlapLock: true,
            ))
            // Or use some fancy attribute/interface + class discovery system
            // to find job definition classes in your codebase.
            ->addJob($this->fooSync->getSchedulerTask());
    }

}
