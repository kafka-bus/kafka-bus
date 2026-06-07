<?php

namespace KafkaBus\Core\Tests;

use KafkaBus\Core\Bus;
use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Bus\Publishers\Router\PublisherRoutes;
use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Bus\ThreadFactory;
use KafkaBus\Core\Bus\ThreadRegistry;
use KafkaBus\Core\Consumers\ConsumerStreamFactory;
use KafkaBus\Core\Producers\ProducerStreamFactory;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Connections\ConnectionRegistryFaker;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ProduceMessageTest
{
    public function canProduceMessage(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $routes = (new PublisherRoutes())
            ->add(new Route(ProducerMessageFaker::class, $topicRegistry->get('products')));

        $bus = new Bus(
            new ThreadRegistry(
                new ConnectionRegistryFaker($connectionFaker),
                new ThreadFactory(
                    new ListenerFactory(
                        new ConsumerStreamFactory(),
                    ),
                    new PublisherFactory(
                        new ProducerStreamFactory(),
                        $routes
                    ),
                )
            ),
            'default'
        );

        $bus->publish(new ProducerMessageFaker('test-message', ['foo' => 'bar'], 5));

        Assert::array($connectionFaker->publishedMessages)
            ->hasCount(1)
            ->hasKeys('production.fact.products.1');

        $message = $connectionFaker->publishedMessages['production.fact.products.1'][0];

        Assert::equals($message->payload, 'test-message');
        Assert::equals($message->partition, 5);
        Assert::equals($message->headers, ['foo' => 'bar']);
    }
}
