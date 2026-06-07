<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Kafka;

use RdKafka\Conf;

final class KafkaConfConverter
{
    /**
     * @param array<string, int|string|null> $options
     * @return Conf
     */
    public function fromArray(array $options): Conf
    {
        $conf = new Conf();

        foreach ($options as $key => $value) {
            $conf->set($key, (string) $value);
        }

        return $conf;
    }
}
