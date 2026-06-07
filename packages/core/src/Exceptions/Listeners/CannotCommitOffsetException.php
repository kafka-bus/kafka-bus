<?php

declare(strict_types=1);

namespace KafkaBus\Core\Exceptions\Listeners;

use LogicException;

final class CannotCommitOffsetException extends LogicException
{
}
