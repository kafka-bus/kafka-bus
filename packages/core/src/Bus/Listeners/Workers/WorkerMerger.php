<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus\Listeners\Workers;

use KafkaBus\Core\Consumers\Messages\NativeMessageFactory;
use KafkaBus\Core\Consumers\Router\ConsumerRoutes;
use KafkaBus\Core\Consumers\Router\RouteHandler;
use KafkaBus\Core\Consumers\Router\Route;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerFactoryInterface;

final readonly class WorkerMerger
{
    public function __construct(
        private MessageHandlerFactoryInterface $messageHandlerFactory,
    ) {
    }

    /**
     * @param non-empty-list<Worker> $workers
     * @return Worker
     */
    public function merge(array $workers): Worker
    {
        if (\count($workers) === 1) {
            return $workers[0];
        }

        $routes = new ConsumerRoutes();

        foreach ($workers as $worker) {
            foreach ($worker->routes->all() as $route) {
                $finalWorker = new Worker(
                    name: $worker->name,
                    routes: (new ConsumerRoutes())
                        ->add($route),
                    options: $worker->options
                );

                $routes->add(new Route(
                    topic: $route->topic,
                    handler: new RouteHandler($this->messageHandlerFactory->create($finalWorker)),
                    messageFactory: new NativeMessageFactory(),
                ));
            }
        }

        return new Worker(
            name: 'grouped-worker',
            routes: $routes,
            options: new Options(
                additionalOptions: $workers[0]->options->additionalOptions,
                autoCommit: $workers[0]->options->autoCommit,
                consumerTimeout: $workers[0]->options->consumerTimeout,
            ),
        );
    }
}
