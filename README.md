# Usage example

~~~
#!/usr/bin/env php
<?php

use alcamo\cli\AbstractCli;
use GetOpt\GetOpt;

include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

class ExampleCli extends AbstractCli
{
    public const OPTIONS =
        [
            'foo' => [ 'f', self::REQUIRED_ARGUMENT, 'Set foo.']
        ]
        + parent::OPTIONS;

    public function innerRun(): int
    {
        if ($this->getOption('foo')) {
            $this->getLogger()
                ->notice('Foo was set to ' . $this->getOption('foo'));
        }

        $this->getLogger()->info('Info message.');

        return 0;
    }
}

exit((new ExampleCli([]))->run());
~~~

This example is contained in this package as a file in the `bin`
directory. If called as `bin/example --help`, it outputs

~~~
Usage: bin/example [options]

Options:
  -f, --foo <arg>  Set foo.
  -h, --help       Show help.
  -q, --quiet      Be less verbose.
  -v, --verbose    Be more verbose.
~~~

The invocation `example --foo 42` outputs something like

~~~
[19:01:38] N Foo was set to 42
~~~

Adding verbosity with `example --foo 42 -v` leads to something like

~~~
[20:02:02.913675] N Foo was set to 42
[20:02:02.913771] I Info message.
~~~

Reducing verbosity with `example --foo 42 -q` creates no output.

This illustrates the basic ideas:
* Options, operands, commands etc. are defined by class constants in
  derived classes. See GetOpt.php for details. The options `--help`,
  `--quiet` and `--verbose` are predefined.
* AbstractCli::run() processes the options, displays the help text if
  requested, provides a logger with a log level depending on the
  verbosity, and executes innerRun().
* You implement innerRun() to do the actual work.
