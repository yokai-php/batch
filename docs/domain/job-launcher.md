# Job Launcher

todo

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\NullJobExecutionStorage;

$launcher = new SimpleJobLauncher(
    new JobRegistry(/* an instance of \Psr\Container\ContainerInterface containing jobs */),
    new JobExecutionFactory(),
    new NullJobExecutionStorage(),
    null /* or an instance of \Psr\EventDispatcher\EventDispatcherInterface */
);

$execution = $launcher->launch('your.job.name', ['job' => ['configuration']]);
```
