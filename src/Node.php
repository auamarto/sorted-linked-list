<?php

declare(strict_types=1);

namespace Auamarto\SortedLinkedList;

/**
 * @internal
 */
final class Node
{
    public function __construct(
        public int|string $value,
        public ?self $next = null,
    ) {
    }
}
