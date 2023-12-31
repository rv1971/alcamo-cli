<?php

namespace alcamo\cli;

use GetOpt\ArgumentException;
use alcamo\exception\Dumper;

/**
 * @brief Base class for command-line interfaces
 *
 * @todo Write unit tests
 *
 * @date Last reviewed 2021-07-19
 */
abstract class AbstractCli extends GetOpt
{
    /// Number of `verbose` options minus number of `quiet` options
    public function getVerbosity(): int
    {
        return $this->getOption('verbose') - $this->getOption('quiet');
    }

    /**
     * @brief Run the program
     *
     * Call showHelp() if the `--help` option was given.
     *
     * Otherwise call innerRun(). If innerRun() throws an exception, it will
     * be displayed in short or long form depending whether the `--verbose`
     * option was given. The exception code will be returned
     *
     * @return exit code
     */
    public function run($arguments = null): int
    {
        try {
            parent::process($arguments);
        } catch (ArgumentException $e) {
            if ($this->getOption('help')) {
                $this->showHelp();
            } else {
                /** If an exception occurs, show the exception message. */
                echo $e->getMessage();
            }

            return 255;
        }

        if ($this->getOption('help')) {
            $this->showHelp();
            return 0;
        }

        try {
            $this->innerRun($arguments);
        } catch (\Throwable $e) {
            if ($this->getVerbosity() > 0) {
                echo (new Dumper())->dump($e) . "\n";
            } else {
                echo $e->getMessage() . "\n\n";
            }

            return $e->getCode() != 0 ? $e->getCode() : 255;
        }
    }

    /// Show the output of getHelpText()
    public function showHelp(): void
    {
        echo $this->getHelpText();
    }

    /**
     * @brief Implementation of the program
     *
     * Called by run() after processing the command line. See run() for
     * details.
     *
     * @return exit code
     */
    abstract public function innerRun(): int;

    /**
     * @brief Output test to stderr if requested by verbosity level
     *
     * @return Whether text was output or not.
     */
    public function reportProgress(
        string $text,
        ?int $minimumVerbosity = null
    ): bool {
        if ($this->getVerbosity() >= (int)$minimumVerbosity) {
            /* fwrite(STDERR, ...) may not work in php installations that are
             * not meant for command-line usage, e.g. on hosted webspace */
            file_put_contents('php://stderr', "$text\n");
            return true;
        }

        return false;
    }
}
