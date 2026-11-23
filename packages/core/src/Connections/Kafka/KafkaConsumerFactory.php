<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Kafka;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\ConsumerHandler;
use KafkaBus\Core\Consumers\ConsumerHandlerInterface;
use RdKafka\KafkaConsumer;

final class KafkaConsumerFactory
{
    private KafkaConfConverter $confConverter;

    public function __construct(
        private readonly Options $options,
    ) {
        $this->confConverter = new KafkaConfConverter();
    }

    public function make(ConsumerConfig $config, ConsumerHandlerInterface $handler = new ConsumerHandler()): KafkaConsumer
    {
        $options = $this->options
            ->getConsumerOptions($config->additionalOptions);

        $options['enable.auto.commit'] = $config->autoCommit ? 'true' : 'false';

        if (!$config->autoCommit) {
            $options['enable.auto.offset.store'] = 'false';
        }

        $conf = $this->confConverter->fromArray($options);
        $conf->setErrorCb($handler->error(...));

        if ($handler instanceof KafkaLoggerInterface) {
            $conf->setLogCb($handler->logging(...));
        }

        return new KafkaConsumer($conf);
    }
}
