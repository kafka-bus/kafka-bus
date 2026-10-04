<?php


use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\Registry\ConnectionRegistry;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Messages\ConsumerHandlerFaker;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;

$topicRegistry = (new TopicRegistry())
    ->add(new Topic('production.fact.products.1', 'products'));

$publisherRoutes = PublisherRoutesBuilder::make($topicRegistry)
    ->add(ProducerMessageFaker::class, 'products')
    ->build();

$receiver = ReceiverBuilder::make($topicRegistry)
    ->add(new RouteInfo('products', new ConsumerHandlerFaker()))
    ->build();

$connectionRegistry = ConnectionRegistry::default();

$bus = new Bus(
    connection: $connectionRegistry->connection('default'),
    publisherFactory: new PublisherFactory(routes: $publisherRoutes),
    receiver: $receiver,
);
