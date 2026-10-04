<?php


use KafkaBus\Core\BusInterface;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Worker\Options as WorkerOptions;
use KafkaBus\Worker\Registry\MemoryWorkerRegistry;
use KafkaBus\Worker\Worker;
use KafkaBus\Worker\WorkerRunnerFactory;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$workerRegistry = MemoryWorkerRegistry::make()
    ->add(new Worker(
        name: 'products-worker',
        topics: [$topicRegistry->get('products')],
        options: new WorkerOptions(
            additionalOptions: [
                'group.id' => 'products-microservice',
                'auto.offset.reset' => 'earliest',
            ],
        ),
    ));

$runner = (new WorkerRunnerFactory($workerRegistry))
    ->create($bus, 'products-worker');

pcntl_async_signals(true);

pcntl_signal(SIGINT, fn () => $runner->forceStop());

$runner->run();
