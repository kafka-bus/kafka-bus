<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage0;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage1;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage2;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage3;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage4;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage5;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage6;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage7;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage8;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage9;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage;
use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\NullConnection;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Workbench\ProductMessage;

/**
 * Сборка Bus для бенчмарков: на каждый из $topics топиков свой маршрут.
 */
final class BusFactory
{
    public static function topicName(int $n): string
    {
        return "production.fact.topic-$n.1";
    }

    /**
     * @return list<class-string<TopicMessage>>
     */
    public static function messageClasses(): array
    {
        return [
            TopicMessage0::class, TopicMessage1::class, TopicMessage2::class, TopicMessage3::class, TopicMessage4::class,
            TopicMessage5::class, TopicMessage6::class, TopicMessage7::class, TopicMessage8::class, TopicMessage9::class,
        ];
    }

    /**
     * Потребление: Bus с NullConnection и маршрутом на каждый топик.
     *
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public static function receiver(int $topics, array $middleware = [], ?MessageFactoryInterface $factory = null, ?callable $handler = null): Bus
    {
        $registry = self::registry($topics);

        $builder = ReceiverBuilder::make($registry, $factory);
        for ($n = 0; $n < $topics; $n++) {
            $builder->add(new RouteInfo("topic-$n", $handler ?? new VoidConsumerHandlerFaker(), $middleware));
        }

        return new Bus(new NullConnection('bench'), receiver: $builder->build());
    }

    /**
     * Публикация: Bus с DrainConnection и маршрутами TopicMessage0..9 → topic-0..9 (опционально ещё ProductMessage → topic-0).
     */
    public static function publisher(Options $options = new Options(), bool $withProductRoute = false): Bus
    {
        $routes = PublisherRoutesBuilder::make(self::registry(\count(self::messageClasses())));

        foreach (self::messageClasses() as $n => $messageClass) {
            $routes->add($messageClass, "topic-$n", $options);
        }

        if ($withProductRoute) {
            $routes->add(ProductMessage::class, 'topic-0');
        }

        return new Bus(new DrainConnection(), new PublisherFactory(routes: $routes->build()));
    }

    private static function registry(int $topics): TopicRegistry
    {
        $registry = new TopicRegistry();
        for ($n = 0; $n < $topics; $n++) {
            $registry->add(new Topic(self::topicName($n), "topic-$n"));
        }

        return $registry;
    }
}
