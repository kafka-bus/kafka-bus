<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Exceptions\Consumers\ErrorConsumerException;
use RdKafka\KafkaConsumer;

final class ConsumerHandler implements ConsumerHandlerInterface
{
    private int $errorCode = 0;

    /**
     * @var array<int, int>
     */
    private array $fatal = [
        // жёсткие фатальные состояния
        RD_KAFKA_RESP_ERR__FATAL,             // -150
        RD_KAFKA_RESP_ERR__CRIT_SYS_RESOURCE, // -194

        // тема/партиция исчезли
        RD_KAFKA_RESP_ERR_UNKNOWN_TOPIC_OR_PART, // 3
        RD_KAFKA_RESP_ERR__UNKNOWN_TOPIC,        // -188
        RD_KAFKA_RESP_ERR__UNKNOWN_PARTITION,    // -190

        // авторизация / SSL / ACL
        RD_KAFKA_RESP_ERR__AUTHENTICATION,             // -169
        RD_KAFKA_RESP_ERR__SSL,                        // -181
        RD_KAFKA_RESP_ERR_SASL_AUTHENTICATION_FAILED,  // 58
        RD_KAFKA_RESP_ERR_TOPIC_AUTHORIZATION_FAILED,  // 29
        RD_KAFKA_RESP_ERR_GROUP_AUTHORIZATION_FAILED,  // 30
        RD_KAFKA_RESP_ERR_CLUSTER_AUTHORIZATION_FAILED,// 31

        // конфигурация / баг кода
        RD_KAFKA_RESP_ERR__INVALID_ARG,    // -186
        RD_KAFKA_RESP_ERR__NOT_CONFIGURED, // -145

        // вытеснен другим экземпляром
        RD_KAFKA_RESP_ERR__FENCED,            // -144
        RD_KAFKA_RESP_ERR_FENCED_INSTANCE_ID, // 82
    ];

    public function error(KafkaConsumer $kafka, int $errorCode, string $errorMessage): void
    {
        $this->errorCode = $errorCode;
    }

    public function throwIfError(): void
    {
        if (\in_array($this->errorCode, $this->fatal, true)) {
            throw new ErrorConsumerException($this->errorCode);
        }
    }
}
