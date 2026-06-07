<?php

declare(strict_types=1);

namespace KafkaBus\Core\Exceptions;

use Exception;

final class CannotSetOffsetForPartitionsException extends Exception
{
    public function __construct(string $message, ?\RdKafka\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
