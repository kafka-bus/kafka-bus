<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Connections;

use KafkaBus\Core\Connections\Config\Options;
use Testo\Assert;
use Testo\Test;

#[Test]
final class OptionsTest
{
    public function producerOptionsPreserveZerosAndRemoveEmptyValues(): void
    {
        $options = new Options([
            'retries' => 0,
            'linger.ms' => '0',
            'transactional.id' => '',
            'compression.type' => null,
            'enable.idempotence' => false,
        ]);

        Assert::same($options->getProducerOptions(), [
            'retries' => 0,
            'linger.ms' => '0',
            'enable.idempotence' => 'false',
        ]);
        Assert::same($options->getProducerOptions([
            'retries' => '0',
            'linger.ms' => 0,
            'enable.idempotence' => true,
            'transactional.id' => '',
            'compression.type' => null,
        ]), [
            'retries' => '0',
            'linger.ms' => 0,
            'enable.idempotence' => 'true',
        ]);
    }

    public function consumerOptionsPreserveZerosAndRemoveEmptyValues(): void
    {
        $options = new Options();

        Assert::same($options->getConsumerOptions([
            'fetch.wait.max.ms' => 0,
            'fetch.error.backoff.ms' => '0',
            'group.id' => '',
            'auto.offset.reset' => null,
            'enable.auto.commit' => false,
            'enable.partition.eof' => true,
        ]), [
            'fetch.wait.max.ms' => 0,
            'fetch.error.backoff.ms' => '0',
            'enable.auto.commit' => 'false',
            'enable.partition.eof' => 'true',
        ]);
    }

    public function trimsStringsAndRemovesWhitespaceOnlyValues(): void
    {
        $options = new Options([
            'transactional.id' => " \t\r\n",
            'client.id' => ' client ',
        ]);

        Assert::same($options->getProducerOptions(), [
            'client.id' => 'client',
        ]);
        Assert::same($options->getConsumerOptions([
            'group.id' => " \t\n",
            'fetch.wait.max.ms' => '0',
        ]), [
            'client.id' => 'client',
            'fetch.wait.max.ms' => '0',
        ]);
    }
}
