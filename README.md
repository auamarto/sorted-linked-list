# SortedLinkedList (ints, ASC)

A tiny PHP library that implements a **sorted (ascending), unique** linked list of integers.

- Values are always kept in **ASC order**.
- Values are **unique** (attempting to insert a duplicate throws).
- Implemented as a classic singly linked list (`Node -> next`), with `head` and `tail` pointers.
- Optional **finger search** can speed up workloads where you search/insert in non-decreasing order.

## Installation

```bash
composer require auamarto/sorted-linked-list
```

## Quick start

```php
<?php

use Auamarto\SortedLinkedList\SortedLinkedList;

$list = new SortedLinkedList();

$list->add(10);
$list->add(5);
$list->add(20);

var_dump($list->toArray()); // [5, 10, 20]

var_dump($list->min());   // 5
var_dump($list->max());   // 20
var_dump($list->count()); // 3

var_dump($list->contains(10)); // true
var_dump($list->remove(10));   // true
var_dump($list->remove(999));  // false

var_dump($list->toArray()); // [5, 20]

$list->clear();
var_dump($list->count()); // 0
```

## API

### Creating a list

#### `new SortedLinkedList()`
Creates an empty list.

#### `SortedLinkedList::fromSortedUnique(iterable $values): SortedLinkedList`
Builds a list from values that are already **strictly ascending and unique**.

This is the fastest way to populate a large list because it links nodes in a single pass.

```php
$list = SortedLinkedList::fromSortedUnique([1, 3, 5, 8]);
```

If the input is not strictly increasing, it throws `InvalidArgumentException`.

### Mutations

#### `add(int $value): void`
Inserts a value into the list.

- Keeps the list sorted.
- Throws `InvalidArgumentException` if the value already exists.

#### `remove(int $value): bool`
Removes the value if present.

- Returns `true` if something was removed.
- Returns `false` if the value wasn’t in the list.

#### `clear(): void`
Removes all items and releases node references.

### Queries

#### `contains(int $value): bool`
Returns `true` when the value is present.

#### `count(): int`
Number of elements in the list.

#### `min(): ?int`
Returns the smallest value or `null` for an empty list.

#### `max(): ?int`
Returns the biggest value or `null` for an empty list.

#### `toArray(): array`
Returns all values in ascending order.

### Finger optimization (optional)

#### `enableFinger(bool $enabled = true): void`
Enables/disables a “finger” (cached position) used by internal search.

This can improve performance when you repeatedly call `add/remove/contains` with values that are **mostly non-decreasing** (e.g. time-series inserts).

Notes:
- It’s an optimization only; it does not change public behavior.
- For random lookups/inserts, the finger may not help.

## Complexity (high level)

Because this is a singly linked list:

- `add`, `remove`, `contains`: **O(n)** worst-case (must traverse until the right spot)
- `min`: **O(1)** (head)
- `max`: **O(1)** (tail)
- `fromSortedUnique`: **O(n)** build

## Development

### Scripts (composer)

All scripts are defined in `composer.json`:

- `composer cs:check`
  - Runs PHP CS Fixer in dry-run mode and shows a diff.

- `composer cs:fix`
  - Fixes formatting via PHP CS Fixer.

- `composer phpcs:check`
  - Runs PHP_CodeSniffer checks using `phpcs.xml`.

- `composer phpcs:fix`
  - Runs PHP_CodeSniffer auto-fixer (`phpcbf`) using `phpcs.xml`.

- `composer stan`
  - Runs PHPStan static analysis using `phpstan.neon`.

- `composer test`
  - Runs PHPUnit using `phpunit.xml`.

- `composer test:performance`
  - Runs the performance test only (guarded by `RUN_PERF_TEST=1`).

- `composer test:coverage`
  - Runs tests with text coverage output and saves Clover XML to `build/coverage/clover.xml`.
  - Uses `php -d xdebug.mode=coverage ...` to enable coverage.

- `composer test:coverage:html`
  - Runs tests and outputs HTML coverage into `build/coverage/html`.

- `composer qa`
  - Runs the full QA pipeline: cs-fixer check, phpcs check, phpstan, and tests.

### Running locally

Typical workflow:

```bash
composer install
composer qa
```

## License

MIT

