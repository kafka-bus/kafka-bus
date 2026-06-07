<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Kafka;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Producers\ProducerConfig;
use RdKafka\Producer as KafkaProducer;

final class KafkaProducerFactory
{
    private KafkaConfConverter $confConverter;

    public function __construct(
        private readonly Options $options,
    ) {
        $this->confConverter = new KafkaConfConverter();
    }

    public function make(ProducerConfig $config): KafkaProducer
    {
        $options = $this->options
            ->getProducerOptions($config->additionalOptions);

        return new KafkaProducer($this->confConverter->fromArray($options));
    }
}
