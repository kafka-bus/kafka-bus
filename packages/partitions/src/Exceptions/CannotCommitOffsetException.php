<?php

declare(strict_types=1);

namespace KafkaBus\Partitions\Exceptions;

use LogicException;

final class CannotCommitOffsetException extends LogicException
{
}
