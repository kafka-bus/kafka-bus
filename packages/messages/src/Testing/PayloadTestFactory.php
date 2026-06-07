<?php

namespace KafkaBus\Messages\Testing;

use KafkaBus\Messages\Data\Payload;

/**
 * @template TPayload of Payload
 */
abstract class PayloadTestFactory extends TestFactory
{
    /**
     * @var class-string<TPayload>
     */
    protected string $payloadClass;

    /**
     * @param array<string, mixed> $extra
     * @return TPayload
     */
    public function payload(array $extra = []): Payload
    {
        return ($this->payloadClass)::from($this->makeArray($extra));
    }
}
