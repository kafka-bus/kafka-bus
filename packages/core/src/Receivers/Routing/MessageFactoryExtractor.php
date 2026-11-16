<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Core\Receivers\Messages\JsonMessageFactory;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\OriginalMessageFactory;
use KafkaBus\Core\Receivers\Messages\ProvidesMessageFactory;
use KafkaBus\Core\Receivers\Messages\StringMessageFactory;
use RdKafka\Message;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class MessageFactoryExtractor
{
    public function extract(callable $handler): ?MessageFactoryInterface
    {
        if ($handler instanceof ProvidesMessageFactory) {
            return $handler->messageFactory();
        }

        $reflection = new ReflectionFunction($handler(...));

        return $this->extractFromAttribute($reflection)
            ?? $this->fromArgs($reflection);
    }

    private function extractFromAttribute(ReflectionFunctionAbstract $reflection): ?MessageFactoryInterface
    {
        /** @var ReflectionAttribute<MessageFactory>|null $attribute */
        $attribute = $reflection->getAttributes(MessageFactory::class)[0] ?? null;

        return $attribute?->newInstance()
            ->messageFactory;
    }

    private function fromArgs(ReflectionFunctionAbstract $reflection): ?MessageFactoryInterface
    {
        $arg = $reflection->getParameters()[0] ?? null;
        $type = $arg?->getType() ?? null;

        if (!$type instanceof ReflectionNamedType) {
            return null;
        }

        return match (true) {
            $type->getName() == 'string' => new StringMessageFactory(),
            $type->getName() == 'array' => new JsonMessageFactory(),
            $type->getName() == Message::class => new OriginalMessageFactory(),
            default => null,
        };
    }
}
