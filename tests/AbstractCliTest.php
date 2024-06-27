<?php

namespace alcamo\cli;

use alcamo\exception\Unsupported;
use PHPUnit\Framework\TestCase;

class MyCli extends AbstractCli
{
    public const OPTIONS = [
        'foo' => [
            'F',
            GetOpt::MULTIPLE_ARGUMENT,
            'Lorem ipsum.',
            'foo'
        ],
        'bar' => [
            'r',
            GetOpt::REQUIRED_ARGUMENT,
            'Dolor sit amet.'
        ],
        'baz' => [
            null,
            GetOpt::NO_ARGUMENT,
            'Consetetur sadipscing.'
        ]
    ] + parent::OPTIONS;

    public function innerRun(): int
    {
        if ($this->getOption('bar')) {
            throw (new Unsupported())->setMessageContext(
                [ 'feature' => $this->getOption('bar') ]
            );
        }

        return 42;
    }
}

class AbstractCliTest extends TestCase
{
    public function testHelp(): void
    {
        $cli = new MyCli();

        $this->expectOutputString(
            "Usage: {$_SERVER['argv'][0]} [options] \n" . <<<EOT

Options:
  -F, --foo <foo>  Lorem ipsum.
  -h, --help       Show help.
  -q, --quiet      Be less verbose.
  -r, --bar <arg>  Dolor sit amet.
  -v, --verbose    Be more verbose.
  --baz            Consetetur sadipscing.


EOT
        );

        $exitCode = $cli->run('--help');

        $this->assertSame(0, $exitCode);
    }

    public function testReportProgress(): void
    {
        $cli = new MyCli();

        $cli->run('');

        /* There is no simply way to test stderr output, so what is tested here
         * is just the verbosity level check. */
        $this->assertFalse($cli->reportProgress('VERBOSITY 1', 1));
    }

    public function testRun(): void
    {
        $cli = new MyCli();

        $this->assertSame(42, $cli->run(''));
    }

    public function testRunException(): void
    {
        $cli = new MyCli();

        $feature = 'foo';

        $this->expectOutputString("\"$feature\" not supported\n\n");

        $cli->run("--bar $feature");
    }
}
