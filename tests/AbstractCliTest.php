<?php

namespace alcamo\cli;

use PHPUnit\Framework\TestCase;

class MyCli extends AbstractCli
{
}

class AbstractCliTest extends TestCase
{
    public function testReportProgress()
    {
        $cli = new MyCli();

        /* There is no simply way to test stderr output, so what is teste here
         * is just the verbosity level check. */
        $this->assertFalse($cli->reportProgress('VERBOSITY 1', 1));
    }
}
