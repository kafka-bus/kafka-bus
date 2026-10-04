<?php


use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\Registry\ConnectionRegistry;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Receivers\Routing\ReceiverRoutesBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Messages\ConsumerHandlerFaker;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;

$topicRegistry = (new TopicRegistry())
    ->add(new Topic('production.fact.products.1', 'products'));

$consumeOptions = [
    'group.id' => 'products-microservice',
    'auto.offset.reset' => 'beginning',
];

$consumerRoutes = ReceiverRoutesBuilder::make($topicRegistry)
    ->add(new RouteInfo('products', new ConsumerHandlerFaker()))
    ->build();

$publisherRoutes = PublisherRoutesBuilder::make($topicRegistry)
    ->add(ProducerMessageFaker::class, 'products')
    ->build();

$workerRegistry = Bus\Listeners\Workers\MemoryWorkerRegistry::make()
    ->add(
        new Bus\Listeners\Workers\Worker(
            'default-listener',
            $consumerRoutes,
            new Bus\Listeners\Workers\Options(additionalOptions: $consumeOptions)
        )
    );

$bus = new Bus(
    new Bus\ThreadRegistry(
        ConnectionRegistry::default(),
        new Bus\ThreadFactory(
            new Bus\Listeners\ListenerFactory(workerRegistry: $workerRegistry),
            new \KafkaBus\Core\Publishers\PublisherFactory(routes: $publisherRoutes),
        )
    ),
    ConnectionRegistry::DEFAULT_CONNECTION_NAME
);
