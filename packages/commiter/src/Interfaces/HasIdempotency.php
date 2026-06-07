<?php

namespace KafkaBus\Commiter\Interfaces;

interface HasIdempotency
{
    public function getIdempotencyKey(): string;
}
