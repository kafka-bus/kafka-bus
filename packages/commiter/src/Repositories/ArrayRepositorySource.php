<?php

namespace KafkaBus\Commiter\Repositories;

use DateTimeImmutable;
use KafkaBus\Commiter\Attempt;
use KafkaBus\Commiter\Interfaces\RepositorySourceInterface;

final class ArrayRepositorySource implements RepositorySourceInterface
{
    /**
     * @var array<string, Attempt>
     */
    protected array $commited = [];

    public function get(string $key): ?Attempt
    {
        return $this->commited[$key] ?? null;
    }

    public function commit(string $key): void
    {
        $attempt = $this->commited[$key] ?? null;

        $this->commited[$key] = $attempt == null
            ? new Attempt($key, 1, new DateTimeImmutable())
            : new Attempt($key, $attempt->number, new DateTimeImmutable());
    }

    public function increment(string $key): void
    {
        $attempt = $this->commited[$key] ?? null;

        $this->commited[$key] = $attempt == null
            ? new Attempt($key, 1)
            : new Attempt($key, $attempt->number + 1);
    }
}
