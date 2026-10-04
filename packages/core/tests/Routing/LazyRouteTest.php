<?php

namespace KafkaBus\Core\Tests\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Receivers\Receiver;
use KafkaBus\Core\Receivers\Routing\ReceiverRouter;
use KafkaBus\Core\Receivers\Routing\Route;
use KafkaBus\Core\Routing\LazyRoute;
use KafkaBus\Core\Routing\RouteCollection;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use LogicException;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class LazyRouteTest
{
    public function buildsRouteOnlyOnFirstAccessAndCachesIt(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $builds = 0;

        $routes = (new RouteCollection(Route::class))
            ->add(new Route($topicRegistry->get('orders'), new VoidConsumerHandlerFaker()))
            ->add(new LazyRoute(
                'events.products.1',
                function () use (&$builds, $topicRegistry): Route {
                    $builds++;

                    return new Route($topicRegistry->get('products'), new VoidConsumerHandlerFaker());
                },
            ));

        Assert::same($builds, 0);

        $receiver = new Receiver(new ReceiverRouter($routes));

        $receiver->dispatch(new ConsumerMessage(
            MessageFactory::for()
                ->withTopicKey('products', $topicRegistry)
                ->make('payload-1'),
        ));

        Assert::same($builds, 1);

        $receiver->dispatch(new ConsumerMessage(
            MessageFactory::for()
                ->withTopicKey('products', $topicRegistry)
                ->make('payload-2'),
        ));

        Assert::same($builds, 1);
    }

    public function throwsWhenFactoryBuildsRouteWithDifferentKey(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $lazyRoute = new LazyRoute(
            'events.orders.1',
            static fn (): Route => new Route($topicRegistry->get('products'), new VoidConsumerHandlerFaker()),
        );

        Expect::exception(LogicException::class)
            ->withMessageContaining('events.orders.1')
            ->withMessageContaining('events.products.1');

        $lazyRoute->resolve();
    }
}
