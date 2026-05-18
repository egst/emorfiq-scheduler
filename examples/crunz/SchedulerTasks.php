<?php declare(strict_types = 1);

/**
 * Crunz scheduler adapter setup boilerplate.
 *
 * The Crunz scheduler requires a separate PHP file that returns the schedule
 * definition. Initialize the Crunz scheduler adapter here and call your
 * application code as needed to provide the scheduler configuration and job definitions.
 * The Scheduler interface and the SchedulerConfig class provide library-agnostic
 * interface to use in your application without any library-specific dependencies.
 */

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../framework/container.php';

use App\SchedulerSetup;
use Egst\EmorfiqScheduler\Scheduler\CrunzScheduler;

$setup     = $container->get(SchedulerSetup::class);
$scheduler = CrunzScheduler::create($setup->getConfig());
$setup->define($scheduler);

return $scheduler->getSchedule();
