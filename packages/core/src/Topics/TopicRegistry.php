<?php

namespace KafkaBus\Core\Topics;

use KafkaBus\Core\Exceptions\TopicCannotResolvedException;

final class TopicRegistry
{
    /**
     * @var Topic[]
     */
    protected array $topics = [];

    public function add(Topic $topic): self
    {
        $this->topics[$topic->key] = $topic;

        return $this;
    }

    public function getTopicName(string $topicKey): string
    {
        return $this->get($topicKey)->name;
    }

    public function tryGetTopicName(string $topicKey): string
    {
        return isset($this->topics[$topicKey])
            ? $this->topics[$topicKey]->name
            : $topicKey;
    }

    public function get(string $topicKey): Topic
    {
        return $this->topics[$topicKey]
            ?? throw TopicCannotResolvedException::topicNotFound($topicKey);
    }
}
