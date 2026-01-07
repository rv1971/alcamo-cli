<?php

namespace alcamo\cli;

use Monolog\Logger as BaseLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * @brief Simple logger for logging to stderr
 *
 * @date Last reviewed 2026-01-07
 */
class Logger extends BaseLogger
{
    /// Map verbosity to Monolog log level
    public const VERBOSITY_TO_LOG_LEVEL = [
        -3 => Logger::CRITICAL,
        -2 => Logger::ERROR,
        -1 => Logger::WARNING,
         0 => Logger::NOTICE,
         1 => Logger::INFO,
         2 => Logger::DEBUG
    ];

    /// Default log message format
    public const FORMAT = "[%datetime%] %extra.level_char% %message%\n";

    /// Default date format in log messages
    public const DATE_FORMAT = 'H:i:s';

    /// Verbose date format in log messages
    public const VERBOSE_DATE_FORMAT = 'H:i:s.u';

    /**
     * @param $verbosity Result of
     * alcamo::cli::AbstractCli::getVerbosity(). The default verbosity of 0
     * means that events of log level NOTICE and higher are logged.
     *
     * @param $stream Output stream passed to the StreamHandler [STDERR].
     *
     * @param $format log message format [alcamo::cli::Logger::FORMAT]
     *
     * @param $dateFormat date format in log messages
     * [alcamo::cli::Logger::FORMAT]
     */
    public function __construct(
        int $verbosity,
        $stream = null,
        ?string $format = null,
        ?string $dateFormat = null
    ) {
        switch (true) {
            case $verbosity < array_key_first(static::VERBOSITY_TO_LOG_LEVEL):
                $logLevel = Logger::CRITICAL;
                break;

            case $verbosity > array_key_last(static::VERBOSITY_TO_LOG_LEVEL):
                $logLevel = Logger::DEBUG;
                break;

            default:
                $logLevel = static::VERBOSITY_TO_LOG_LEVEL[$verbosity];
        }


        $handler = new StreamHandler($stream ?? STDERR, $logLevel);

        $handler->setFormatter(
            new LineFormatter(
                $format ?? static::FORMAT,
                $dateFormat ??
                ($verbosity > 0
                 ? static::VERBOSE_DATE_FORMAT
                 : static::DATE_FORMAT)
            )
        );

        parent::__construct('cli', [ $handler ]);

        /** Create a new variable `level_char` in the extra data which
         *  contains the first character of the log level. */
        $this->pushProcessor(
            function (array $record): array {
                $record['extra']['level_char'] = $record['level_name'][0];

                return $record;
            }
        );
    }
}
