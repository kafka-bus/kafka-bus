<?php

namespace KafkaBus\Core\Producers\Messages;

interface HasHeaders
{
    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;
}
