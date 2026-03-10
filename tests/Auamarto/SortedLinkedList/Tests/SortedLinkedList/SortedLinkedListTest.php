<?php

declare(strict_types=1);

namespace Auamarto\SortedLinkedList\Tests\SortedLinkedList;

use Auamarto\SortedLinkedList\SortedLinkedList;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SortedLinkedListTest extends TestCase
{
    public function testEmptyListContracts(): void
    {
        $list = new SortedLinkedList();

        self::assertSame(0, $list->count());
        self::assertSame([], $list->toArray());
        self::assertNull($list->min());
        self::assertNull($list->max());
        self::assertFalse($list->contains(123));
        self::assertFalse($list->remove(123));
    }

    public function testAddKeepsAscendingOrder(): void
    {
        $list = new SortedLinkedList();

        $list->add(10);
        $list->add(30);
        $list->add(20);
        $list->add(5);
        $list->add(40);

        self::assertSame([5, 10, 20, 30, 40], $list->toArray());
        self::assertSame(5, $list->min());
        self::assertSame(40, $list->max());
        self::assertSame(5, $list->count());
    }

    public function testAddThrowsWhenElementAlreadyExists(): void
    {
        $list = new SortedLinkedList();
        $list->add(10);

        $this->expectException(InvalidArgumentException::class);
        $list->add(10);
    }

    public function testContainsWorks(): void
    {
        $list = new SortedLinkedList();

        foreach ([10, 30, 20] as $v) {
            $list->add($v);
        }

        self::assertTrue($list->contains(10));
        self::assertTrue($list->contains(20));
        self::assertTrue($list->contains(30));

        self::assertFalse($list->contains(5));
        self::assertFalse($list->contains(25));
        self::assertFalse($list->contains(35));
    }

    public function testRemoveRewiresLinksAndDecrementsCount(): void
    {
        $list = new SortedLinkedList();
        foreach ([10, 20, 30, 40] as $v) {
            $list->add($v);
        }
        self::assertSame([10, 20, 30, 40], $list->toArray());
        self::assertSame(4, $list->count());

        // remove head
        self::assertTrue($list->remove(10));
        self::assertSame([20, 30, 40], $list->toArray());
        self::assertSame(3, $list->count());
        self::assertSame(20, $list->min());

        // remove middle
        self::assertTrue($list->remove(30));
        self::assertSame([20, 40], $list->toArray());
        self::assertSame(2, $list->count());

        // remove tail
        self::assertTrue($list->remove(40));
        self::assertSame([20], $list->toArray());
        self::assertSame(1, $list->count());
        self::assertSame(20, $list->max());

        // remove missing
        self::assertFalse($list->remove(999));
        self::assertSame([20], $list->toArray());
        self::assertSame(1, $list->count());

        // remove last element
        self::assertTrue($list->remove(20));
        self::assertSame([], $list->toArray());
        self::assertSame(0, $list->count());
        self::assertNull($list->min());
        self::assertNull($list->max());
    }

    public function testClearEmptiesListAndResetsContracts(): void
    {
        $list = new SortedLinkedList();
        foreach ([10, 20, 30] as $v) {
            $list->add($v);
        }

        self::assertSame(3, $list->count());
        self::assertSame([10, 20, 30], $list->toArray());

        $list->clear();

        self::assertSame(0, $list->count());
        self::assertSame([], $list->toArray());
        self::assertNull($list->min());
        self::assertNull($list->max());
        self::assertFalse($list->contains(10));

        $list->clear();
        self::assertSame(0, $list->count());
        self::assertSame([], $list->toArray());

        $list->add(5);
        $list->add(1);
        self::assertSame([1, 5], $list->toArray());
        self::assertSame(2, $list->count());
    }

    public function testFromSortedUniqueBuildsCorrectList(): void
    {
        $list = SortedLinkedList::fromSortedUnique([1, 3, 10, 100]);

        self::assertSame([1, 3, 10, 100], $list->toArray());
        self::assertSame(4, $list->count());
        self::assertSame(1, $list->min());
        self::assertSame(100, $list->max());
        self::assertTrue($list->contains(10));
        self::assertFalse($list->contains(2));
    }

    public function testFromSortedUniqueAllowsEmptyIterable(): void
    {
        $list = SortedLinkedList::fromSortedUnique([]);

        self::assertSame([], $list->toArray());
        self::assertSame(0, $list->count());
        self::assertNull($list->min());
        self::assertNull($list->max());
    }

    public function testFromSortedUniqueThrowsWhenNotStrictlyAscendingOrNotUnique(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SortedLinkedList::fromSortedUnique([1, 2, 2, 3]);
    }

    public function testAddKeepsAscendingOrderForStrings(): void
    {
        $list = new SortedLinkedList();

        $list->add('banana');
        $list->add('apple');
        $list->add('cherry');

        self::assertSame(['apple', 'banana', 'cherry'], $list->toArray());
        self::assertSame('apple', $list->min());
        self::assertSame('cherry', $list->max());
        self::assertSame(3, $list->count());

        self::assertTrue($list->contains('banana'));
        self::assertFalse($list->contains('date'));
    }

    public function testTypeIsLockedPerListInstance(): void
    {
        $list = new SortedLinkedList();
        $list->add(1);

        $this->expectException(InvalidArgumentException::class);
        $list->add('2');
    }

    public function testFromSortedUniqueThrowsWhenIterableContainsMixedTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SortedLinkedList::fromSortedUnique([1, '2', 3]);
    }

    public function testClearResetsTypeLock(): void
    {
        $list = new SortedLinkedList();

        $list->add(1);
        $list->clear();

        $list->add('a');
        $list->add('b');

        self::assertSame(['a', 'b'], $list->toArray());
    }
}
