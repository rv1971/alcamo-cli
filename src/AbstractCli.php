<?php

namespace alcamo\cli;

use GetOpt\ArgumentException;

/**
 * @brief Base class for command-line interfaces
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
     * @brief Process arguments
     *
     * Call showHelp() if the `help` option was given.
     */
    public function process($arguments = null)
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

            exit;
        }

        if ($this->getOption('help')) {
            $this->showHelp();
            exit;
        }
    }

    /// Show the output of getHelpText()
    public function showHelp()
    {
        echo $this->getHelpText();
    }
}
