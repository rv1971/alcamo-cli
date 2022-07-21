<?php

namespace alcamo\cli;

use PHPUnit\Framework\TestCase;

class MyGetOpt extends GetOpt
{
    public const SETTINGS = [
        self::SETTING_STRICT_OPERANDS => true
    ];
}

class GetOptTest extends TestCase
{
    public function testSettings()
    {
        $getOpt = new MyGetOpt();

        $this->assertTrue($getOpt->get(GetOpt::SETTING_STRICT_OPERANDS));
    }
}
