<?php

namespace alcamo\cli;

use GetOpt\Operand;
use GetOpt\ArgumentException\Invalid;
use PHPUnit\Framework\TestCase;

class MyGetOpt extends GetOpt
{
    public const DESCRIPTION = 'Lorem ipsum dolor sit amet, '
        . 'consetetur sadipscing elitr, sed diam nonumy eirmod tempor '
        . 'invidunt ut labore et dolore magna aliquyam erat, '
        . 'sed diam voluptua.';

    public const OPTIONS =
        [
            'foo' => [
                'f',
                self::REQUIRED_ARGUMENT,
                'Perform foo.'
            ],
            'bar' => [
                'b',
                self::OPTIONAL_ARGUMENT,
                'Perform bar.',
                'no',
                'is_numeric'
            ],
            'baz' => [
                null,
                self::NO_ARGUMENT,
                'Perform baz.'
            ]
        ]
        + parent::OPTIONS;

    public const OPERANDS = [
        'infile' => Operand::REQUIRED,
        'outfile' => Operand::MULTIPLE
    ];

    public const COMMANDS = [
        'qux' => [
            'doQux',
            [
                'quux' => [ 'Q', self::NO_ARGUMENT, 'Activate Quux.' ]
            ],
            [
                'target' => Operand::MULTIPLE
            ],
            'Run qux'
        ]
    ];
}

class GetOptTest extends TestCase
{
    public function testProps(): void
    {
        $getOpt = new MyGetOpt();

        /* Test description. */

        $this->assertStringContainsString(
            'Lorem ipsum',
            $getOpt->getHelp()->render($getOpt)
        );

        /* Test options. */

        $options = $getOpt->getOptionObjects();

        $this->assertSame(7, count($options));

        $longNames = [];
        $shortNames = [];
        $descriptions = [];

        foreach ($options as $option) {
            $longNames[] = $option->getLong();
            $shortNames[] = $option->getShort();
            $descriptions[] = $option->getDescription();
        }

        $this->assertSame(
            [ 'bar', 'foo', 'help', 'quiet', 'verbose', 'version', 'baz' ],
            $longNames
        );

        $this->assertSame(
            [ 'b', 'f', 'h', 'q', 'v', 'V', null ],
            $shortNames
        );

        $this->assertSame(
            [
                'Perform bar.',
                'Perform foo.',
                'Show help.',
                'Be less verbose.',
                'Be more verbose.',
                'Show version.',
                'Perform baz.'
            ],
            $descriptions
        );

        $this->assertSame(
            'no',
            $getOpt->getOptionObject('bar')->getArgument()->getName()
        );

        /* Test operands. */

        $this->assertSame(2, count($getOpt->getOperandObjects()));

        $operands = [];

        foreach ($getOpt->getOperandObjects() as $operand) {
            $operands[$operand->getName()] = [
                $operand->isRequired(),
                $operand->isMultiple()
            ];
        }

        $this->assertSame(
            [
                'infile' => [ true, false ],
                'outfile' => [ false, true ]
            ],
            $operands
        );

        /* Test commands. */

        $this->assertSame(1, count($getOpt->getCommands()));

        $command = $getOpt->getCommand('qux');

        $this->assertSame('doQux', $command->getHandler());

        $this->assertSame(
            'Activate Quux.',
            $command->getOption('Q')->getDescription()
        );

        $this->assertSame(1, count($command->getOperands()));

        $this->assertTrue($command->getOperand('target')->isMultiple());

        /* Test settings. */

        $this->assertTrue($getOpt->get(GetOpt::SETTING_STRICT_OPERANDS));
    }

    public function testOptionValidation(): void
    {
        $getOpt = new MyGetOpt();

        $argument = $getOpt->getOptionObject('bar')->getArgument();

        $this->assertSame($argument, $argument->setValue(42));

        $this->expectException(Invalid::class);

        $argument->setValue('foo');
    }
}
