<?php

declare(strict_types=1);

namespace KafkaBus\Core\Testing;

use KafkaBus\Core\Bus;
use KafkaBus\Core\BusInterface;
use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Receivers\Receiver;
use KafkaBus\Core\Receivers\ReceiverInterface;
use KafkaBus\Core\Testing\Assertions\AssertionDriverInterface;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Receivers\ReceiverFaker;
use KafkaBus\Core\Topics\TopicRegistry;
use RdKafka\Message;

final class BusFaker implements BusInterface
{
    public function __construct(
        private readonly Bus $bus,
        private readonly ConnectionFaker $connectionFaker,
        private readonly ReceiverFaker $receiverFaker,
        private readonly TopicRegistry $topicRegistry,
        private readonly AssertionDriverInterface $driver,
    ) {
    }

    public static function make(
        TopicRegistry $topicRegistry,
        AssertionDriverInterface $driver,
        PublisherFactory $publisherFactory = new PublisherFactory(),
        ReceiverInterface $receiver = new Receiver(),
    ): self {
        $connectionFaker = new ConnectionFaker();
        $receiverFaker = new ReceiverFaker($receiver);

        return new self(
            new Bus($connectionFaker, $publisherFactory, $receiverFaker),
            $connectionFaker,
            $receiverFaker,
            $topicRegistry,
            $driver,
        );
    }

    public function connection(): ConnectionInterface
    {
        return $this->bus->connection();
    }

    public function publish(ProducerMessageInterface $message): void
    {
        $this->bus->publish($message);
    }

    public function publishBatch(MessageBatch $messageBatch): void
    {
        $this->bus->publishBatch($messageBatch);
    }

    public function dispatch(ConsumerMessageInterface $message): void
    {
        $newMessage = $message->original();
        $newMessage->topic_name = $this->topicRegistry->tryGetTopicName($message->topicName());

        $this->bus->dispatch(new ConsumerMessage($newMessage));
    }

    public function addMessage(Message $message): void
    {
        $message->topic_name = $this->topicRegistry
            ->tryGetTopicName($message->topic_name);

        $this->connectionFaker->addMessage($message);
    }

    /**
     * @param class-string $messageClass
     * @param (callable(ProducerMessage): bool)|null $callback
     */
    public function assertPublished(string $messageClass, ?callable $callback = null): void
    {
        $messages = $this->getPublished($messageClass);

        $this->driver->notEmpty($messages, "Expected [$messageClass] to be published, but it was not.");

        if ($callback !== null) {
            $this->driver->notEmpty(
                array_values(array_filter($messages, $callback)),
                "[$messageClass] was published but no message matched the given condition.",
            );
        }
    }

    /**
     * @param class-string $messageClass
     */
    public function assertPublishedTimes(string $messageClass, int $times): void
    {
        $messages = $this->getPublished($messageClass);

        $this->driver->count($messages, $times, \sprintf(
            'Expected [%s] to be published %d time(s), but it was published %d time(s).',
            $messageClass,
            $times,
            \count($messages),
        ));
    }

    /**
     * @param class-string $messageClass
     */
    public function assertNotPublished(string $messageClass): void
    {
        $this->driver->count(
            $this->getPublished($messageClass),
            0,
            "Expected [$messageClass] not to be published, but it was.",
        );
    }

    public function assertNothingPublished(): void
    {
        $this->driver->count($this->allPublished(), 0, 'Expected no messages to be published, but some were.');
    }

    /**
     * @param class-string $messageClass
     * @return list<ProducerMessage>
     */
    public function getPublished(string $messageClass): array
    {
        $topicName = $this->topicNameFor($messageClass);

        return $topicName === null ? [] : $this->connectionFaker->publishedMessages[$topicName] ?? [];
    }

    /**
     * @return list<ProducerMessage>
     */
    public function allPublished(): array
    {
        if ($this->connectionFaker->publishedMessages === []) {
            return [];
        }

        return array_merge(...array_values($this->connectionFaker->publishedMessages));
    }

    /**
     * @param (callable(ConsumerMessageInterface): bool)|null $callback
     */
    public function assertCommitted(string $topicKey, ?callable $callback = null): void
    {
        $messages = $this->getCommitted($topicKey);

        $this->driver->notEmpty($messages, "Expected a message on topic [$topicKey] to be committed, but none was.");

        if ($callback !== null) {
            $this->driver->notEmpty(
                array_values(array_filter($messages, $callback)),
                "A message on topic [$topicKey] was committed but none matched the given condition.",
            );
        }
    }

    public function assertCommittedTimes(string $topicKey, int $times): void
    {
        $messages = $this->getCommitted($topicKey);

        $this->driver->count($messages, $times, \sprintf(
            'Expected %d committed message(s) on topic [%s], but got %d.',
            $times,
            $topicKey,
            \count($messages),
        ));
    }

    public function assertNothingCommitted(): void
    {
        $all = $this->connectionFaker->committedMessages === []
            ? []
            : array_merge(...array_values($this->connectionFaker->committedMessages));

        $this->driver->count($all, 0, 'Expected no messages to be committed, but some were.');
    }

    /**
     * @return list<ConsumerMessageInterface>
     */
    public function getCommitted(string $topicKey): array
    {
        $topicName = $this->topicRegistry->getTopicName($topicKey);

        return $this->connectionFaker->committedMessages[$topicName] ?? [];
    }

    /**
     * @param (callable(ConsumerMessageInterface): bool)|null $callback
     */
    public function assertDispatched(string $topicKey, ?callable $callback = null): void
    {
        $messages = $this->getDispatched($topicKey);

        $this->driver->notEmpty($messages, "Expected a message on topic [$topicKey] to be dispatched, but none was.");

        if ($callback !== null) {
            $this->driver->notEmpty(
                array_values(array_filter($messages, $callback)),
                "A message on topic [$topicKey] was dispatched but none matched the given condition.",
            );
        }
    }

    public function assertDispatchedTimes(string $topicKey, int $times): void
    {
        $messages = $this->getDispatched($topicKey);

        $this->driver->count($messages, $times, \sprintf(
            'Expected %d dispatched message(s) on topic [%s], but got %d.',
            $times,
            $topicKey,
            \count($messages),
        ));
    }

    public function assertNotDispatched(string $topicKey): void
    {
        $this->driver->count(
            $this->getDispatched($topicKey),
            0,
            "Expected no message on topic [$topicKey] to be dispatched, but one was.",
        );
    }

    public function assertNothingDispatched(): void
    {
        $this->driver->count($this->allDispatched(), 0, 'Expected no messages to be dispatched, but some were.');
    }

    /**
     * @return list<ConsumerMessageInterface>
     */
    public function getDispatched(string $topicKey): array
    {
        $topicName = $this->topicRegistry->getTopicName($topicKey);

        return $this->receiverFaker->dispatchedMessages[$topicName] ?? [];
    }

    /**
     * @return list<ConsumerMessageInterface>
     */
    public function allDispatched(): array
    {
        if ($this->receiverFaker->dispatchedMessages === []) {
            return [];
        }

        return array_merge(...array_values($this->receiverFaker->dispatchedMessages));
    }

    /**
     * @param class-string $messageClass
     */
    private function topicNameFor(string $messageClass): ?string
    {
        foreach ($this->bus->publisher()->routes() as $route) {
            if ($route->messageClass === $messageClass) {
                return $route->topic->name;
            }
        }

        return null;
    }
}
