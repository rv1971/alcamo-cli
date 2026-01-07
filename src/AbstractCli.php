<?php

namespace alcamo\cli;

use alcamo\exception\Dumper;
use GetOpt\ArgumentException;
use Monolog\Logger as MonologLogger;

/**
 * @brief Base class for command-line interfaces
 *
 * @date Last reviewed 2021-07-19
 */
abstract class AbstractCli extends GetOpt
{
    private $verbosity_; ///< int
    private $logger_; ///< Logger

    /// Count of `--verbose` minus count of `--quiet`
    public function getVerbosity(): int
    {
        return $this->verbosity_;
    }

    public function getLogger(): MonologLogger
    {
        return $this->logger_;
    }

    /**
     * This is needed only for loggers with custom settings.
     */
    public function setLogger(MonologLogger $logger): void
    {
        $this->logger_ = $logger;
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

        /* Create e default logger unless it has already been set. */
        if (!isset($this->logger_)) {
            $this->setLogger(new Logger($this->verbosity_));
        }
    }

    /**
     * @brief Run the program
     *
     * Call showHelp() if the `--help` option was given.
     *
     * Otherwise call innerRun(). If innerRun() throws an exception, it will
     * be displayed in short or long form depending whether the `--verbose`
     * option was given. The exception code will be returned.
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
                    $this->setLogger(new Logger(0));
                }

                $this->logger_->critical($e->getMessage());
            }

            return 255;
        }

        if ($this->getOption('help')) {
            $this->showHelp();
            return 0;
        }

        try {
            return $this->innerRun();
        } catch (\Throwable $e) {
            if ($this->verbosity_ > 0) {
                $this->logger_->critical((new Dumper())->dump($e));
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

    /**
     * @brief Implementation of the program
     *
     * Called by run() after processing the command line. See run() for
     * details.
     *
     * @return exit code
     */
    abstract public function innerRun(): int;
}
