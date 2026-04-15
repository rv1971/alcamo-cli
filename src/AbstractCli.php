<?php

namespace alcamo\cli;

use alcamo\exception\Dumper;
use Composer\InstalledVersions;
use GetOpt\ArgumentException;
use Monolog\Logger as MonologLogger;

/**
 * @brief Base class for command-line interfaces
 *
 * @date Last reviewed 2021-07-19
 */
abstract class AbstractCli extends GetOpt
{
    use LoggableTrait;

    private $verbosity_ = 0; ///< int

    /// Count of `--verbose` minus count of `--quiet`
    public function getVerbosity(): int
    {
        return $this->verbosity_;
    }

    /**
     * @brief Processess command line, compute verbosity, create logger
     *
     * The log level of the logger is set based on the verbosity (see
     * getVerbosity()).
     */
    public function process($arguments = null)
    {
        parent::process($arguments);

        $this->verbosity_ =
            $this->getOption('verbose') - $this->getOption('quiet');

        if (!isset($this->logger_)) {
            $this->setLogger($this->createLogger());
        }
    }

    /**
     * @brief Run the program
     *
     * Call showHelp() if
     * - the `--help` option was given
     * - or there are sub-commands defined in alcamo::cli::GetOpt::COMMANDS,
     *   but no command was given on the command line.
     *
     * Call showVersion() if the `--version` option was given.
     *
     * Otherwise call innerRun(). If innerRun() throws an exception, it will
     * be displayed in short or long form depending whether the `--verbose`
     * option was given. In this case, return exit code 255.
     *
     * If there are sub-commands defined:
     * - If innerRun() returns a nonzero exit code, terminate with that code.
     * - Otherwise call the handler for the given sub-command.
     * Hence, in CLIs with sub-commands, innerRun() is used to execute any
     * common code needed by all sub-commands.
     *
     * @return exit code
     */
    public function run($arguments = null): int
    {
        try {
            $this->process($arguments);
        } catch (ArgumentException $e) {
            if ($this->getOption('help')) {
                $this->showHelp();
            } else {
                /** If an exception occurs, show the exception message. */
                if (!isset($this->logger_)) {
                    $this->setLogger($this->createLogger());
                }

                $this->logger_->critical($e->getMessage());
            }

            return 255;
        }

        if ($this->getOption('help')) {
            $this->showHelp();
            return 0;
        }

        if ($this->getOption('version')) {
            $this->showVersion();
            return 0;
        }

        if (static::COMMANDS && !$this->getCommand()) {
            $this->showHelp();
            return 0;
        }

        try {
            if (static::COMMANDS) {
                $exitCode = $this->innerRun();

                if ($exitCode) {
                    return $exitCode;
                } else {
                    return $this->{$this->getCommand()->getHandler()}();
                }
            } else {
                return $this->innerRun();
            }
        } catch (\Throwable $e) {
            if ($this->verbosity_ > 0) {
                foreach (explode("\n", (new Dumper())->dump($e)) as $line) {
                    if ($line) {
                        $this->logger_->critical($line);
                    }
                }

                if ($this->verbosity_ > 1) {
                    foreach (explode("\n", $e->getTraceAsString()) as $line) {
                        if ($line) {
                            $this->logger_->critical($line);
                        }
                    }
                }
            } else {
                $this->logger_->critical($e->getMessage());
            }

            return $e->getCode() != 0 ? $e->getCode() : 255;
        }
    }

    /// Show the output of getHelpText()
    public function showHelp(): void
    {
        echo $this->getHelpText();
    }

    public function showVersion(): void
    {
        $rootPackage = InstalledVersions::getRootPackage();

        $version = exec('git describe --tags');

        if (!$version) {
            $version = $rootPackage['version'];
        }

        echo "{$rootPackage['name']} $version" . PHP_EOL;
    }

    /**
     * @brief Implementation of the program
     *
     * Called by run() after processing the command line. See run() for
     * details.
     *
     * The present implementation simply returns 0 so that CLIs with
     * sub-commands do not need to re-implement innerRun() if they do not have
     * any common code to execute before the sub-command handler.
     *
     * @return exit code
     */
    public function innerRun(): int
    {
        return 0;
    }

    protected function createLogger(): MonologLogger
    {
        return new Logger($this->verbosity_);
    }
}
