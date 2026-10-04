<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use RdKafka\Message;

final class Kafka
{
    public static function rawMessage(string $topic, string $payload, int $offset = 0): Message
    {
        $message = new Message();
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $message->payload = $payload;
        $message->headers = ['source' => 'benchmark'];
        $message->key = 'key';
        $message->partition = 0;
        $message->offset = $offset;
        $message->topic_name = $topic;

        return $message;
    }

    public static function message(string $topic, string $payload, int $offset = 0): ConsumerMessage
    {
        return new ConsumerMessage(self::rawMessage($topic, $payload, $offset));
    }
}
