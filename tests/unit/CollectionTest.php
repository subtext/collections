<?php

namespace Subtext\Collections\Test;

use PHPUnit\Framework\TestCase;
use Subtext\Collections\Collection;
use Subtext\Collections\ExampleItem;
use Subtext\Collections\Exceptions\InvalidCollectionItemException;


class CollectionTest extends TestCase
{
    public function testCanValidateIntegerArray()
    {
        $input = [0, 1, 2, 3, 4, 5];
        $unit = new  class($input) extends Collection {
            protected $type = 'integer';
        };
        $this->assertCount(6, $unit);
        $this->assertInstanceOf(\Traversable::class, $unit);
        $input = [0, 1, '2', 3, 4, 5];
        $this->expectException(InvalidCollectionItemException::class);
        $unit = new class($input) extends Collection {
            protected $type = 'integer';
        };
    }

    public function testCanValidateFQDN()
    {
        $one = new ExampleItem();

        $two = new ExampleItem();
        $unit = new class([$one, $two]) extends Collection {
            protected $type = 'object';
            protected $namespace = ExampleItem::class;
        };
        $this->assertCount(2, $unit);
    }
}
