<?php

namespace Subtext\Collections;

use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testCanIterateInstance(): void
    {
        $unit = new Collection([]);
        $this->assertEquals(0, $unit->count());
        $this->assertInstanceOf(\Traversable::class, $unit);
    }
}
