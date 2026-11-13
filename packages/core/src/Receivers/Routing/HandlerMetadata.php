<?php

namespace KafkaBus\Core\Receivers\Routing;

use Closure;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use RdKafka\Message;
use ReflectionFunction;
use ReflectionNamedType;
use LogicException;

final readonly class HandlerMetadata
{
    /**
     * @param array<string, string> $metadataParameters
     */
    public function __construct(private array $metadataParameters)
    {
    }

    public static function fromHandler(Closure $handler): self
    {
        $metadataParameters = [];
        $reflection = new ReflectionFunction($handler);

        foreach (\array_slice($reflection->getParameters(), 1) as $parameter) {
            if ($parameter->isVariadic()) {
                continue;
            }

            $name = $parameter->getName();
            $type = $parameter->getType();

            $source = match ($type instanceof ReflectionNamedType ? $type->getName() : null) {
                ConsumerMessageInterface::class => 'record',
                Message::class => 'original',
                default => match ($name) {
                    'headers', 'offset', 'partition', 'key', 'topic', 'topicName', 'payload' => $name,
                    default => null,
                },
            };

            if ($source !== null) {
                $metadataParameters[$name] = $source;
            }
        }

        return new self($metadataParameters);
    }

    /**
     * @param ConsumerMessageInterface $record
     * @return array<string, mixed>
     */
    public function arguments(ConsumerMessageInterface $record): array
    {
        return array_map(function ($source) use ($record) {
            return match ($source) {
                'headers' => $record->headers(),
                'offset' => $record->original()->offset,
                'partition' => $record->original()->partition,
                'key' => $record->key(),
                'topic', 'topicName' => $record->topicName(),
                'original' => $record->original(),
                'record' => $record,
                'payload' => $record->payload(),
                default => new LogicException("Unknown metadata parameter: {$source}")
            };
        }, $this->metadataParameters);
    }
}
