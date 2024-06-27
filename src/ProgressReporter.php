<?php

namespace alcamo\cli;

class ProgressReporter
{
    private $verbosity_; ///< int

    public function __construct(?int $verbosity = null)
    {
        $this->verbosity_ = (int)$verbosity;
    }

    public function getVerbosity(): int
    {
        return $this->verbosity_;
    }

    /**
     * @brief Output test to stderr if requested by verbosity level
     *
     * @return Whether text was output or not.
     */
    public function write(
        string $text,
        ?int $minimumVerbosity = null
    ): bool {
        if ($this->verbosity_ >= (int)$minimumVerbosity) {
            /* fwrite(STDERR, ...) may not work in php installations that are
             * not meant for command-line usage, e.g. on hosted webspace */
            file_put_contents('php://stderr', "$text\n");
            return true;
        }

        return false;
    }
}
