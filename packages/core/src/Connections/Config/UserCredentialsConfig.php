<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Config;

use KafkaBus\Core\Interfaces\Connections\SaslConfigurationConfigInterface;

final readonly class UserCredentialsConfig implements SaslConfigurationConfigInterface
{
    public function __construct(
        public string $username,
        public string $password,
        public string $protocol = 'plaintext',
        public string $mechanisms = 'PLAIN'
    ) {
    }

    public function toOptions(): array
    {
        return [
            'security.protocol' => $this->protocol,
            'sasl.mechanisms' => $this->mechanisms,
            'sasl.username' => $this->username,
            'sasl.password' => $this->password,
        ];
    }
}
