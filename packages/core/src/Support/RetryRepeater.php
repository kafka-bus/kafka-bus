<?php

namespace KafkaBus\Core\Support;

use Throwable;

final class RetryRepeater
{
    public function __construct(
        protected int $maximumRetries = 10,
        protected int $sleepAfterError = 1000
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(callable $function): void
    {
        $this->attempt($function);
    }

    /**
     * @throws Throwable
     */
    private function attempt(callable $function, int $attempt = 1): void
    {
        try {
            $function();
        }
        catch (Throwable $exception) {
            if ($attempt >= $this->maximumRetries) {
                throw $exception;
            }

            usleep($this->sleepAfterError);

            $this->attempt($function, ++$attempt);
        }
    }
}
