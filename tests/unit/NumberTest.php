<?php

namespace Subtext\Collections\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Subtext\Collections\Number;

class NumberTest extends TestCase
{
    public function testCanValidate(): void
    {
        $unit = new Number([1, 2, 3]);
        $this->assertInstanceOf(Number::class, $unit);
        $this->assertEquals(3, $unit->count());

        $this->expectException(\InvalidArgumentException::class);
        new Number(['alpha', 'beta', 'gamma']);
    }

    public function testCanNormalizeType(): void
    {
        $mixed   = [1, 2.9, 4.87560, '5.6', '9'];
        $integer = [1, 2, 4, 5, 9];
        $float   = [1, 2.9, 4.87560, 5.6, 9];

        $unit = new Number($mixed);
        $this->assertEquals($integer, $unit->getIntegers()->getArrayCopy());
        $this->assertEquals($float, $unit->getFloats()->getArrayCopy());
    }
}
