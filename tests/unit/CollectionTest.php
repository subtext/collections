<?php

namespace Subtext\Collections\Tests\Unit;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Subtext\Collections\Collection;
use Subtext\Collections\NotFoundException;
use Traversable;

final class FooBar implements JsonSerializable {
    public function __construct(private int|string $id)
    {
    }

    public function jsonSerialize(): mixed
    {
        return $this->id;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function setId(int|string $id): void
    {
        $this->id = $id;
    }
};

class CollectionTest extends TestCase
{
    private static ?Generator $faker = null;
    private ?Collection $unit = null;

    public static function setUpBeforeClass(): void
    {
        self::$faker = Factory::create();
    }

    protected function setUp(): void
    {
        $this->unit = new class extends Collection {
            protected function validate(mixed $value): void
            {
                if (!($value instanceof FooBar)) {
                    throw new InvalidArgumentException(sprintf(
                        'Value must be an instance of %s',
                        FooBar::class
                    ));
                }
            }
        };
        parent::setUp();
    }

    public function testCanCreateInstance(): void
    {
        $this->assertInstanceOf(Collection::class, new class([new FooBar(0)]) extends Collection {
            protected function validate(mixed $value): void
            {
                if (!($value instanceof FooBar)) {
                    throw new InvalidArgumentException(sprintf(
                        'Value must be an instance of %s',
                        FooBar::class
                    ));
                }
            }
        });
    }

    public function testCanValidateValue(): void
    {
        $this->assertEquals(0, $this->unit->count());
        $this->assertInstanceOf(Traversable::class, $this->unit);

        $this->unit->append(new FooBar(1));
        $this->assertEquals(1, $this->unit->count());
        $this->assertInstanceOf(FooBar::class, $this->unit->getFirst());

        $this->expectException(InvalidArgumentException::class);
        $this->unit->append(null);
    }

    public function testCanSerializeContentsToJson(): void
    {
        $this->unit->set('alpha', new FooBar(1));
        $this->unit->set('beta', new FooBar(2));
        $this->unit->set('gamma', new FooBar(3));
        $expected = '{"alpha":1,"beta":2,"gamma":3}';

        $this->assertEquals($expected, json_encode($this->unit));

        $this->unit->renameKey('alpha', 0);
        $this->unit->renameKey('beta', 1);
        $this->unit->renameKey('gamma', 2);
        $expected = '[1,2,3]';

        $this->assertEquals($expected, json_encode($this->unit));
    }

    public function testCanAbsorbAnotherCollection(): void
    {
        $this->unit->set(1, new FooBar(1));
        $this->unit->set(2, new FooBar(2));

        $other = new class extends Collection {
            protected function validate(mixed $value): void
            {
                if (!($value instanceof FooBar)) {
                    throw new InvalidArgumentException(sprintf(
                        'Value must be an instance of %s',
                        FooBar::class
                    ));
                }
            }
        };
        $other->set(3, new FooBar(3));
        $this->unit->absorb($other);

        $this->assertEquals(3, $this->unit->count());
        $this->assertEquals(3, $this->unit->getLast()->getId());

        $this->unit->empty();
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->absorb($other);

        $this->assertEquals(3, $this->unit->count());
        $this->assertEquals(3, $this->unit->getLast()->getId());
    }

    public function testCanUseContainerInterface(): void
    {
        $this->unit->set(1, new FooBar(1));
        $this->unit->set(2, new FooBar(2));
        $this->unit->set(3, new FooBar(3));

        $this->assertTrue($this->unit->has(1));
        $this->assertTrue($this->unit->has(2));
        $this->assertTrue($this->unit->has(3));

        $this->assertInstanceOf(FooBar::class, $this->unit->get(1));
        $this->assertInstanceOf(FooBar::class, $this->unit->get(2));
        $this->assertInstanceOf(FooBar::class, $this->unit->get(3));

        $this->expectException(NotFoundException::class);
        $this->unit->get(4);
    }

    public function testCanGetKeys(): void
    {
        $expected = array_unique(
            self::$faker->words(self::$faker->numberBetween(3, 10))
        );
        foreach ($expected as $key) {
            $this->unit->set($key, new FooBar(1));
        }
        $this->assertEquals($expected, $this->unit->getKeys());
    }

    public function testCanGetOrdinalMembers(): void
    {
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(4));
        $this->unit->append(new FooBar(5));
        $this->unit->append(new FooBar(6));

        $this->assertTrue($this->unit->getFirst()->getId() === 1);
        $this->assertTrue($this->unit->getNth(4)->getId() === 4);
        $this->assertTrue($this->unit->getLast()->getId() === 6);
        $this->assertTrue($this->unit->getNth(7) === null);
    }

    public function testCanKnowIfEmpty(): void
    {
        $this->assertTrue($this->unit->isEmpty());
        $this->unit->append(new FooBar(1));
        $this->assertFalse($this->unit->isEmpty());
    }

    public function testCanGetSlice(): void
    {
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(4));
        $this->unit->append(new FooBar(5));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(7));
        $this->unit->append(new FooBar(8));
        $this->unit->append(new FooBar(9));
        $this->unit->append(new FooBar(10));

        $actual = $this->unit->slice(3, 5);
        $this->assertEquals(5, $actual->count());
        $expected = '[4,5,6,7,8]';
        $this->assertEquals($expected, json_encode($actual));
    }

    public function testCanCreateChunks(): void
    {
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(4));
        $this->unit->append(new FooBar(5));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(7));
        $this->unit->append(new FooBar(8));
        $this->unit->append(new FooBar(9));
        $this->unit->append(new FooBar(10));

        $actual = $this->unit->chunk(2);
        $this->assertCount(5, $actual);
        $this->assertEquals('[1,2]', json_encode($actual[0]));
        $this->assertEquals('[3,4]', json_encode($actual[1]));
        $this->assertEquals('[5,6]', json_encode($actual[2]));
        $this->assertEquals('[7,8]', json_encode($actual[3]));
        $this->assertEquals('[9,10]', json_encode($actual[4]));
    }

    public function testCanFilterCollection(): void
    {
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(6));
        $this->unit->append(new FooBar(9));
        $this->unit->append(new FooBar(9));
        $this->unit->append(new FooBar(9));

        $actual = $this->unit->filter(function ($item) {
            return $item->getId() === 6;
        });

        $this->assertEquals(4, $actual->count());
        foreach ($actual as $item) {
            $this->assertEquals(6, $item->getId());
        }
    }

    public function testCanMapCollection(): void
    {
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(4));
        $this->unit->append(new FooBar(5));

        $actual = $this->unit->map(function (FooBar $item) {
            $n     = $item->getId();
            $cubed = ($n * $n * $n);
            return new FooBar($cubed);
        });

        $this->assertEquals('[1,8,27,64,125]', json_encode($actual));
    }

    public function testCanWalkCollection(): void
    {
        foreach (range(0, ($max = self::$faker->numberBetween(4, 9))) as $i) {
            $this->unit->set($i, new FooBar(self::$faker->unique()->colorName()));
        }
        $this->unit->walk(function (Foobar $item, mixed $key): void {
            $item->setId(sprintf('The color is: %s', $item->getId()));
        });
        foreach ($this->unit as $value) {
            $this->assertStringContainsString('The color is:', $value->getId());
        }
    }

    public function testCanReduceCollection(): void
    {
        $this->unit->append(new FooBar(1));
        $this->unit->append(new FooBar(2));
        $this->unit->append(new FooBar(3));
        $this->unit->append(new FooBar(4));
        $this->unit->append(new FooBar(5));

        $actual = $this->unit->reduce(function (mixed $carry, FooBar $item): FooBar {
            if ($carry instanceof FooBar) {
                $value = $carry->getId() + $item->getId();
                return new FooBar($value);
            } else {
                return $item;
            }
        });
        $this->assertEquals(15, $actual->getId());
    }
}
