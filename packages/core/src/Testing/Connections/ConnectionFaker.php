<?php

namespace KafkaBus\Core\Testing\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Producers\ProducerInterface;
use KafkaBus\Core\Testing\ConsumerFaker;
use KafkaBus\Core\Testing\ProducerFaker;
use KafkaBus\Core\Topics\Topic;
use RdKafka\Message;
use RdKafka\Message as KafkaMessage;

class ConnectionFaker implements ConnectionInterface
{
    /**
     * @var array<string, list<ProducerMessage>>
     */
    public array $publishedMessages = [];

    /**
     * @var array<string, list<ConsumerMessageInterface>>
     */
    public array $committedMessages = [];

    /**
     * @var array<int, Message>
     */
    protected array $consumeMessages = [];

    public function addMessage(KafkaMessage $message): void
    {
        $this->consumeMessages[] = $message;
    }

    public function getName(): string
    {
        return 'faker';
    }

    public function getOptions(): Options
    {
        return new Options();
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        return new ProducerFaker($this, $topic->name);
    }

    public function createConsumer(ConsumerConfig $config): ConsumerInterface
    {
        return new ConsumerFaker($this, new ConsumerMessageConverter(), $this->consumeMessages);
    }
}
