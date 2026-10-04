<?php

declare(strict_types=1);

namespace KafkaBus\Worker;

use KafkaBus\Core\Topics\Topic;

final readonly class WorkerMerger
{
    /**
     * @param non-empty-list<Worker> $workers
     * @return Worker
     */
    public function merge(array $workers): Worker
    {
        if (\count($workers) === 1) {
            return $workers[0];
        }

        /** @var array<string, Topic> $topics */
        $topics = [];

        foreach ($workers as $worker) {
            foreach ($worker->topics as $topic) {
                $topics[$topic->name] = $topic;
            }
        }

        return new Worker(
            name: 'grouped-worker',
            topics: array_values($topics),
            options: $workers[0]->options,
        );
    }
}
