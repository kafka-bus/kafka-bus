<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\LazyReceiver;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Receiver;
use KafkaBus\Core\Testing\Receivers\ReceiverFaker;
use KafkaBus\Core\Topics\TopicRegistry;

final class ReceiverBuilder
{
    public function __construct(
        private ReceiverRoutesBuilder $routesBuilder,
    ) {
    }

    public function add(RouteInfo $routeInfo): self
    {
        $this->routesBuilder->add($routeInfo);
        return $this;
    }

    public static function make(TopicRegistry $registry, ?MessageFactoryInterface $factory = null): ReceiverBuilder
    {
        return new self(ReceiverRoutesBuilder::make($registry, $factory));
    }

    public function build(): Receiver
    {
        return new Receiver(new ReceiverRouter($this->routesBuilder->build()));
    }

    public function fake(): ReceiverFaker
    {
        return new ReceiverFaker($this->build());
    }

    public function lazy(): LazyReceiver
    {
        return new LazyReceiver($this->build(...));
    }
}
