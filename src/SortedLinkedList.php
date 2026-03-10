<?php

declare(strict_types=1);

namespace Auamarto\SortedLinkedList;

use InvalidArgumentException;

use function assert;
use function is_int;

final class SortedLinkedList
{
    private ?Node $head = null;
    private ?Node $tail = null;
    private int $count = 0;

    /** @var 'int'|'string'|null */
    private ?string $valueType = null;

    private bool $fingerEnabled = false;
    private ?Node $fingerPrevious = null;
    private ?Node $fingerCurrent = null;
    private int|string|null $fingerValue = null;

    /**
     * @param iterable<int|string> $sortedUniqueValues Values must be strictly ascending and unique.
     */
    public static function fromSortedUnique(iterable $sortedUniqueValues): self
    {
        $list = new self();

        $tail = null;
        $hasPrev = false;
        $prev = null;

        foreach ($sortedUniqueValues as $value) {
            $list->isSupportedType($value);

            if ($hasPrev) {
                assert($prev !== null);
                if ($value <= $prev) {
                    throw new InvalidArgumentException('Values must be strictly ascending and unique.');
                }
            }

            $node = new Node($value);

            if ($list->head === null) {
                $list->head = $node;
                $tail = $node;
            } else {
                assert($tail !== null);
                $tail->next = $node;
                $tail = $node;
            }

            $list->count++;
            $prev = $value;
            $hasPrev = true;
        }

        $list->tail = $tail;

        return $list;
    }

    public function enableFinger(bool $enabled = true): void
    {
        $this->fingerEnabled = $enabled;

        if (!$enabled) {
            $this->resetFinger();
        }
    }

    public function add(int|string $value): void
    {
        $this->isSupportedType($value);

        [$previous, $current] = $this->findWithPrevious($value);

        if ($current !== null && $current->value === $value) {
            throw new InvalidArgumentException('Element already exists.');
        }

        $node = new Node($value, $current);

        if ($previous === null) {
            $this->head = $node;
        } else {
            $previous->next = $node;
        }

        if ($node->next === null) {
            $this->tail = $node;
        }

        if ($this->tail === null) {
            // list was empty before insert
            $this->tail = $node;
        }

        $this->count++;

        $this->updateFingerAfterMutation($node, $previous);
    }

    public function remove(int|string $value): bool
    {
        $this->isSupportedType($value);

        [$previous, $current] = $this->findWithPrevious($value);

        if ($current === null || $current->value !== $value) {
            return false;
        }

        if ($previous === null) {
            $this->head = $current->next;
        } else {
            $previous->next = $current->next;
        }

        if ($this->tail === $current) {
            $this->tail = $previous;
        }

        if ($this->fingerEnabled && ($this->fingerCurrent === $current || $this->fingerPrevious === $current)) {
            $this->resetFinger();
        }

        $current->next = null;

        $this->count--;

        if ($this->count === 0) {
            $this->head = null;
            $this->tail = null;
            $this->valueType = null;
            $this->resetFinger();
        }

        return true;
    }

    public function contains(int|string $value): bool
    {
        $this->isSupportedType($value);

        [, $current] = $this->findWithPrevious($value);
        return $current !== null && $current->value === $value;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function min(): int|string|null
    {
        return $this->head?->value;
    }

    public function max(): int|string|null
    {
        return $this->tail?->value;
    }

    /**
     * Clears the list and releases node references.
     */
    public function clear(): void
    {
        $current = $this->head;
        while ($current !== null) {
            $next = $current->next;
            $current->next = null;
            $current = $next;
        }

        $this->head = null;
        $this->tail = null;
        $this->count = 0;
        $this->valueType = null;
        $this->resetFinger();
    }

    /**
     * Returns all values in ascending order.
     *
     * @return list<int|string>
     */
    public function toArray(): array
    {
        $values = [];
        $current = $this->head;
        while ($current !== null) {
            $values[] = $current->value;
            $current = $current->next;
        }

        return $values;
    }

    /**
     * Finds the node whose value is >= $value, and also returns its previous node.
     *
     * Because the list is sorted ASC, it stops early once current value exceeds $value.
     *
     * @return array{0: ?Node, 1: ?Node} [previous, current]
     */
    private function findWithPrevious(int|string $value): array
    {
        $previous = null;
        $current = $this->head;

        if ($this->fingerEnabled && $this->fingerCurrent !== null && $this->fingerValue !== null) {
            if ($value >= $this->fingerValue) {
                $previous = $this->fingerPrevious;
                $current = $this->fingerCurrent;
            }
        }

        while ($current !== null && $current->value < $value) {
            $previous = $current;
            $current = $current->next;
        }

        if ($this->fingerEnabled) {
            $this->fingerPrevious = $previous;
            $this->fingerCurrent = $current;
            $this->fingerValue = $current?->value;
        }

        return [$previous, $current];
    }

    private function resetFinger(): void
    {
        $this->fingerPrevious = null;
        $this->fingerCurrent = null;
        $this->fingerValue = null;
    }

    private function updateFingerAfterMutation(Node $newCurrent, ?Node $newPrevious): void
    {
        if (!$this->fingerEnabled) {
            return;
        }

        $this->fingerPrevious = $newPrevious;
        $this->fingerCurrent = $newCurrent;
        $this->fingerValue = $newCurrent->value;
    }

    private function isSupportedType(int|string $value): void
    {
        $normalized = is_int($value) ? 'int' : 'string';

        if ($this->valueType === null) {
            $this->valueType = $normalized;
            return;
        }

        if ($this->valueType !== $normalized) {
            throw new InvalidArgumentException(\sprintf(
                'This list stores only %s values; %s given.',
                $this->valueType,
                $normalized,
            ));
        }
    }
}
