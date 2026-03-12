<?php

namespace alcamo\cli;

use Monolog\Logger as MonologLogger;

/**
 * @brief Trait to inject a Monolog logger
 *
 * @date Last reviewed 2026-03-12
 */
trait LoggableTrait
{
    private $logger_; ///< ?Logger

    public function hasLogger(): bool
    {
        return isset($this->logger_);
    }

    public function getLogger(): ?MonologLogger
    {
        return $this->logger_;
    }

    public function setLogger(?MonologLogger $logger): void
    {
        $this->logger_ = $logger;
    }
}
