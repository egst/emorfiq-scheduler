# Scheduler Adapter

**A PHP scheduler adapter library with swappable locking backend**

> ⚠️ This is a proof-of-concept interface design. The implementation is
> untested and might not be fully functional. The provided store
> implementations have no implementation.

This library provides a unified interface over the following schedulers:

- [Laravel Scheduling](https://laravel.com/docs/13.x/scheduling)
- [Crunz](https://github.com/crunzphp/crunz)

The `Scheduler` interface allows you to specify configuration via
`SchedulerConfig` and define jobs via `Job` with a library-agnostic interface.

## Dependencies

This library has no required dependencies. See the suggested dependencies in
[`composer.json`](https://github.com/egst/emorfiq-scheduler/blob/master/composer.json)
for the dependencies you'll need with the libraries you choose to use.

## Setup

Set up a cron job to run every minute as instructed by the selected library's documentation.

Initialize the `Scheduler` adapter for the selected library via the `create`
method. This method might require different input depending on the library. The
common configuration is provided via `SchedulerConfig`.

```php
$scheduler = CrunzScheduler::create($myApp->getConfig());
```

Don't forget to specify the timezone in the config. The rest is optional.

Now you can pass the scheduler to your application to use it via the
library-agnostic `Scheduler` interface.

```php
$myApp->defineTasks($scheduler);
```

Make sure to follow the setup instructions specified in the selected scheduler
implementation for library-specific details.

- [`LaravelScheduler`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Scheduler/LaravelScheduler.php)
- [`CrunzScheduler`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Scheduler/CrunzScheduler.php)

## Job Definitions

You can define the jobs to be executed via the library-agnostic [`Scheduler`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Scheduler.php) interface.
The `Schedule::addJob` method accepts a [`Job`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Job.php) object.

```php
function defineTasks (Scheduler $scheduler): void {
    $scheduler->addJob(new Job(
        'hello-world',
        new CronExpression('0 * * * *'),
        static fn () => print('Hello world!' . PHP_EOL),
    ));
}
```

## Locking

Two locking backend abstractions are used depending on what locking semantics
are required by the available libraries.

- Locks with no ownership: [`SharedStore`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Locking/Store/SharedStore.php)
    - Laravel
- Locks with ownership: [`LockProvider`](https://github.com/egst/emorfiq-scheduler/blob/master/src/LockProvider.php)
    - Crunz

These are set up in the `ScheduleConfig` when initializing the scheduler.

```php
new SchedulerConfig(
    timeZone:      $this->config->get('app.timezone'),
    sharedLocking: new PdoStore($this->pdo),
    ownedLocking:  new SymfonyLockProvider(new RedisStore($this->redisClient)),
)
```

You can implement your own `SharedStore` or use what's available in [src/Locking/Store/](https://github.com/egst/emorfiq-scheduler/tree/master/src/Locking/Store).

You can also implement your own `LockProvider` and a corresponding `LockHandle`
but for most common backends you'll only need to implement a `TokenStore` or
`SessionStore` and use one of the provided adapters:
- [`TokenLockProvider`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Locking/TokenLock/TokenLockProvider.php)
- [`SessionLockProvider`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Locking/SessionLock/SessionLockProvider.php)

There is also an adapter for any Symfony Lock backend (a `PersistingStoreInterface` implementation):
- [`SymfonyLockProvider`](https://github.com/egst/emorfiq-scheduler/blob/master/src/Locking/SymfonyLock/SymfonyLockProvider.php)

----


