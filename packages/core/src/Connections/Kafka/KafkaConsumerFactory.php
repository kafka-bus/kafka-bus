<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Kafka;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use RdKafka\KafkaConsumer;

final class KafkaConsumerFactory
{
    private KafkaConfConverter $confConverter;

    public function __construct(
        private readonly Options $options,
    ) {
        $this->confConverter = new KafkaConfConverter();
    }

    public function make(ConsumerConfig $config): KafkaConsumer
    {
        $options = $this->options
            ->getConsumerOptions($config->additionalOptions);

        $options['enable.auto.commit'] = $config->autoCommit ? 'true' : 'false';

        return new KafkaConsumer($this->confConverter->fromArray($options));
    }
}
