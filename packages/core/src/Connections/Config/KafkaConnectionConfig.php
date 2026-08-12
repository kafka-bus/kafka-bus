<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Config;

use KafkaBus\Core\Interfaces\Connections\ConnectionConfigInterface;

final readonly class KafkaConnectionConfig implements ConnectionConfigInterface
{
    /**
     * @param string $brokerList
     * @param int $logLevel
     * @param bool $debug
     * @param UserCredentialsConfig|null $saslConfig
     * @param array<string, string|int|bool|null> $extra
     */
    public function __construct(
        public string $brokerList,
        public int $logLevel = LOG_DEBUG,
        public bool $debug = false,
        public ?UserCredentialsConfig $saslConfig = null,
        public array $extra = [],
    ) {
    }

    public function getOptions(): Options
    {
        /** @var array<string, string|int|bool> $options */
        $options = [
            'metadata.broker.list' => $this->brokerList,
            'log_level' => $this->logLevel,
            'debug' =>  $this->debug ? 'all' : null,
        ];

        $saslOptions = $this->saslConfig?->toOptions() ?? [];

        return new Options(array_merge($options, $saslOptions, $this->extra));
    }
}
