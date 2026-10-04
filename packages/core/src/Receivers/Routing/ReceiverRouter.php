<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\RouteConsumerException;
use KafkaBus\Core\Routing\AbstractRouter;
use KafkaBus\Core\Routing\RouteCollection;
use Throwable;

/**
 * @extends AbstractRouter<Route, RouteExecutor>
 */
final class ReceiverRouter extends AbstractRouter
{
    public function __construct(RouteCollection $routes = new RouteCollection(Route::class))
    {
        parent::__construct($routes);
    }

    public function handle(ConsumerMessageInterface $consumerMessage): void
    {
        $this->resolve($consumerMessage->topicName())
            ->execute($consumerMessage);
    }

    protected function build(string $key, mixed $route): RouteExecutor
    {
        return new RouteExecutor($route->handler, $route->messageFactory, $route->middleware);
    }

    protected function routeNotFound(string $key): Throwable
    {
        return new RouteConsumerException("Route for topic [$key] not found.");
    }
}
