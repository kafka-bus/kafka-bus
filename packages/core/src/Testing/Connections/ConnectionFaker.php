<?php

namespace KafkaBus\Core\Testing\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Testing\ConsumerFaker;
use KafkaBus\Core\Testing\ProducerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
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

    private Options $options;

    private string $name;

    public function __construct(private readonly TopicRegistry $topicRegistry)
    {
        $this->name = 'faker';
        $this->options = new Options([]);
    }

    public function addMessage(KafkaMessage $message): void
    {
        $message->topic_name = $this->topicRegistry
            ->tryGetTopicName($message->topic_name);

        $this->consumeMessages[] = $message;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        return new ProducerFaker($this, $topic->name);
    }

    public function createConsumer(array $topics, ConsumerConfig $config): ConsumerInterface
    {
        return new ConsumerFaker($this, new ConsumerMessageConverter(), $this->consumeMessages);
    }
}
