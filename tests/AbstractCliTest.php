<?php

namespace alcamo\cli;

use alcamo\exception\Unsupported;
use PHPUnit\Framework\TestCase;

class MyCli extends AbstractCli
{
    public const OPTIONS = [
        'foo' => [
            'F',
            self::MULTIPLE_ARGUMENT,
            'Lorem ipsum.',
            'foo'
        ],
        'bar' => [
            'r',
            self::REQUIRED_ARGUMENT,
            'Dolor sit amet.'
        ],
        'baz' => [
            null,
            self::NO_ARGUMENT,
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

/* This also tests the Logger class. */
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

    /**
     * @dataProvider loggerProvider
     */
    public function testLogger(
        $cmdLine,
        $expectedVerbosity,
        $expectedLevel
    ): void {
        $cli = new MyCli();

        $cli->run($cmdLine);

        $this->assertSame($expectedVerbosity, $cli->getVerbosity());

        $this->assertSame(
            $expectedLevel,
            $cli->getLogger()->getHandlers()[0]->getLevel()
        );
    }

    public function loggerProvider(): array
    {
        return [
            [ '', 0, Logger::NOTICE ],
            [ '-v', 1, Logger::INFO ],
            [ '-q -v -v -v', 2, Logger::DEBUG ],
            [ '-vvv', 3, Logger::DEBUG ],
            [ '-v -q -q', -1, Logger::WARNING ],
            [ '-qq', -2, Logger::ERROR ],
            [ '-qqq', -3, Logger::CRITICAL ],
            [ '-qqqq', -4, Logger::CRITICAL ]
        ];
    }

    public function testRun(): void
    {
        $cli = new MyCli();

        $this->assertSame(42, $cli->run(''));
    }

    public function testProcessException(): void
    {
        $logfile = __DIR__ . DIRECTORY_SEPARATOR . 'AbstractCli.log';

        $cli = new MyCli();

        if (file_exists($logfile)) {
            unlink($logfile);
        }

        $cli->setLogger(new Logger(0, $logfile));

        $cli->run("--qux");

        $this->assertStringContainsString(
            "] C Option 'qux' is unknown",
            file_get_contents($logfile)
        );

        unlink($logfile);
    }

    public function testRunException(): void
    {
        $logfile = __DIR__ . DIRECTORY_SEPARATOR . 'AbstractCli.log';

        $cli = new MyCli();

        if (file_exists($logfile)) {
            unlink($logfile);
        }

        $cli->setLogger(new Logger(0, $logfile));

        $feature = 'foo';

        $cli->run("--bar $feature");

        $this->assertStringContainsString(
            "] C \"$feature\" not supported",
            file_get_contents($logfile)
        );

        unlink($logfile);
    }
}
