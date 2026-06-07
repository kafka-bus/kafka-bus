<?php

namespace KafkaBus\Messages\Testing;

use Faker\Factory;
use Faker\Generator;
use JsonException;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use RdKafka\Message;

abstract class TestFactory
{
    /**
     * @var array<string, mixed>
     */
    protected array $states = [];

    protected MessageFactory $messageFactory;

    final public function __construct(
        protected Generator $faker,
    ) {
        $this->messageFactory = new MessageFactory();
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    public function makeArray(array $extra = []): array
    {
        return array_merge($this->definition(), $this->states, $extra);
    }

    /**
     * @param array<string, mixed> $extra
     * @return Message
     *
     * @throws JsonException
     */
    public function make(array $extra = []): Message
    {
        return $this->messageFactory->fromArray($this->makeArray($extra));
    }

    /**
     * @param array<string, mixed> $state
     * @return $this
     */
    public function withState(array $state): static
    {
        return $this->immutableSet('states', array_merge($this->states, $state));
    }

    /**
     * @param array<string, string> $headers
     * @return $this
     */
    public function withHeaders(array $headers): static
    {
        return $this->immutableSet('messageFactory', $this->messageFactory->withHeaders($headers));
    }

    public function withKey(?string $key): static
    {
        return $this->immutableSet('messageFactory', $this->messageFactory->withKey($key));
    }

    public function withTopicKey(?string $topicKey): static
    {
        return $this->immutableSet('messageFactory', $this->messageFactory->withTopicKey($topicKey));
    }

    public function withPartition(int $partition): static
    {
        return $this->immutableSet('messageFactory', $this->messageFactory->withPartition($partition));
    }

    public function withOffset(int $offset): static
    {
        return $this->immutableSet('messageFactory', $this->messageFactory->withOffset($offset));
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    protected function immutableSet(string $field, mixed $value): static
    {
        $clone = clone $this;
        $clone->$field = $value;

        return $clone; // @phpstan-ignore-line
    }

    /**
     * @return static
     */
    public static function new(): self
    {
        return new static(Factory::create('ru_RU'));
    }
}
