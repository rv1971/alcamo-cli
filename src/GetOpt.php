<?php

/**
 * @namespace alcamo\cli
 *
 * @brief Simplify creation of command-line interfaces
 *
 * @todo Write unit tests
 */

namespace alcamo\cli;

use GetOpt\{Command, GetOpt as GetOptBase, Operand, Option};

/**
 * @brief GetOpt extension coding the whole structure in class constants
 *
 * @date Last reviewed 2021-07-19
 */
class GetOpt extends GetOptBase
{
    /// Input for createOptionsFromIterable()
    public const OPTIONS = [
        'help' =>    [ 'h', self::NO_ARGUMENT, 'Show help' ],
        'quiet' =>   [ 'q', self::NO_ARGUMENT, 'Be less verbose' ],
        'verbose' => [ 'v', self::NO_ARGUMENT, 'Be more verbose' ]
    ];

    /// Input for createOperandsFromIterable()
    public const OPERANDS = [];

    /// Input for createCommandsFromIterable()
    public const COMMANDS = [];

    /// Defaults for the $setting given to __construct
    public const SETTINGS = [
        self::SETTING_STRICT_OPERANDS => true
    ];

    public function __construct($options = null, array $settings = [])
    {
        parent::__construct($options, $settings + static::SETTINGS);

        $this
            ->addOptions($this->createOptionsFromIterable(static::OPTIONS))
            ->addOperands($this->createOperandsFromIterable(static::OPERANDS))
            ->addCommands($this->createCommandsFromIterable(static::COMMANDS));
    }

    /**
     * @brief Create array of GetOpt::Option from iterable
     *
     * @param $optionData Map of long option names to numerically-indexed
     * arrays consisting of
     * - short name (potentially `null`)
     * - mode
     * - description
     * - optionally argument name
     * - optionally validation callback
     *
     * Options are sorted case-insensitively as follows: first options with a
     * short version, sorted by short version, then all other options, sorted
     * by long version.
     */
    public function createOptionsFromIterable(iterable $optionData): array
    {
        $options = [];

        foreach ($optionData as $long => $d) {
            $option = (new Option($d[0], $long, $d[1]))->setDescription($d[2]);

            if (isset($d[3])) {
                $option->setArgumentName($d[3]);
            }

            if (isset($d[4])) {
                $option->setValidation($d[4]);
            }

            $options[strtolower(($d[0] ?? '~') . $long)] = $option;
        }

        ksort($options);

        return $options;
    }

    /**
     * @brief Create array of GetOpt::Operand from iterable
     *
     * @param $optionData Map of operand names to modes.
     */
    public function createOperandsFromIterable(iterable $operandData): array
    {
        $operands = [];

        foreach ($operandData as $name => $mode) {
            $operands[] = new Operand($name, $mode);
        }

        return $operands;
    }

    /**
     * @brief Create array of GetOpt::Command from iterable
     *
     * @param $optionData Map of command names to numerically-indexed
     * arrays consisting of
     * - handler
     * - options as input to createOptionsFromIterable()
     * - operands as input to createOperandsFromIterable()
     * - description
     */
    public function createCommandsFromIterable(iterable $commandData): array
    {
        $commands = [];

        foreach ($commandData as $name => $d) {
            $commands[] =
                (
                    new Command(
                        $name,
                        $d[0],
                        $this->createOptionsFromIterable($d[1])
                    )
                )
                ->addOperands($this->createOperandsFromIterable($d[2]))
                ->setShortDescription($d[3]);
        }

        return $commands;
    }
}
