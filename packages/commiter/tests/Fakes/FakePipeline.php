<?php

namespace KafkaBus\Commiter\Tests\Fakes;

use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;

final class FakePipeline implements PipelineInterface
{
    public bool $continued = false;

    public function __construct(
        private PipelineHandlerInterface $handler,
    ) {
    }

    public function handler(): PipelineHandlerInterface
    {
        return $this->handler;
    }

    public function continue(): PipelineInterface
    {
        $this->continued = true;

        return $this;
    }
}
