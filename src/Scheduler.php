<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

/**
 * Generic scheduler interface.
 *
 * Adapter implementations take care of library-specific calls needed to schedule the given job.
 * Each adapter has to be constructed separately with the necessary library-specific inputs.
 * The adapters provide a static create method that accepts a generic SchedulerConfig object
 * for the common configuration options.
 *
 * @see Egst\EmorfiqScheduler\Scheduler\CrunzScheduler
 * @see Egst\EmorfiqScheduler\Scheduler\LaravelScheduler
 */
interface Scheduler {

    public function addJob (Job $task): self;

}
