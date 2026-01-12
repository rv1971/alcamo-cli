<?php

namespace alcamo\cli;

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testWriteEmptyLine(): void
    {
        $stream = fopen('php://memory', 'w+');

        $logger = new Logger(0, $stream);

        $logger->writeLine();

        $logger->writeLine('foo');

        fseek($stream, 0);

        $this->assertSame(PHP_EOL, fgets($stream));

        $this->assertSame('foo' . PHP_EOL, fgets($stream));
    }
}
