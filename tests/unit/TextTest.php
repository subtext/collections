<?php

namespace Subtext\Collections\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Subtext\Collections\Text;

class TextTest extends TestCase
{
    public function testValidateContents(): void
    {
        $unit = new Text(['alpha', 'beta', 'gamma']);
        $this->assertInstanceOf(Text::class, $unit);
        $this->assertEquals(3, $unit->count());

        $this->expectException(\InvalidArgumentException::class);
        new Text([1, 2, 3]);
    }

    public function testCanConcatenateStrings(): void
    {
        $unit = new Text(['alpha', 'beta', 'gamma']);
        $this->assertEquals('alpha, beta, gamma', $unit->concat());
    }
}
