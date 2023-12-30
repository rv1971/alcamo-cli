<?php

namespace alcamo\cli;

use PHPUnit\Framework\TestCase;

class GetOptTest extends TestCase
{
    public function testSettings()
    {
        $getOpt = new GetOpt();

        $this->assertTrue($getOpt->get(GetOpt::SETTING_STRICT_OPERANDS));
    }
}
