<?php

namespace alcamo\cli;

use GetOpt\{Command, GetOpt as GetOptBase, Operand, Option};

class GetOpt extends GetOptBase
{
    public const OPTIONS = [
        'help' =>    [ 'h',  GetOpt::NO_ARGUMENT, 'Show help' ],
        'quiet' =>   [ 'q', GetOpt::NO_ARGUMENT, 'Be less verbose' ],
        'verbose' => [ 'v', GetOpt::NO_ARGUMENT, 'Be more verbose' ]
    ];

    public const OPERANDS = [];

    public const COMMANDS = [];

    public function __construct($options = null, array $settings = [])
    {
        parent::__construct($options, $settings);

        $this->addOptions($this->createOptionsFromIterable(static::OPTIONS));

        $this->addOperands($this->createOperandsFromIterable(static::OPERANDS));

        $this->addCommands($this->createCommandsFromIterable(static::COMMANDS));
    }

    public function createOptionsFromIterable(iterable $optionData): array
    {
        $options = [];

        foreach ($optionData as $long => $d) {
            $options[] =
                (new Option($d[0], $long, $d[1]))->setDescription($d[2]);
        }

        return $options;
    }

    public function createOperandsFromIterable(iterable $operandData): array
    {
        $operands = [];

        foreach ($operandData as $name => $mode) {
            $operands[] = new Operand($name, $mode);
        }

        return $operands;
    }

    public function createCommandsFromIterable(iterable $commandData): array
    {
        $commands = [];

        foreach ($commandData as $name => $d) {
            $commands[] = (
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
