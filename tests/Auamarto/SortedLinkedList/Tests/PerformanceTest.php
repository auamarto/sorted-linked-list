<?php

declare(strict_types=1);

namespace Auamarto\SortedLinkedList\Tests;

use Auamarto\SortedLinkedList\SortedLinkedList;
use PHPUnit\Framework\TestCase;

/**
 * This is skipped by default because it can be slow/heavy.
 * Enable with RUN_PERF_TEST=1.
 */
final class PerformanceTest extends TestCase
{
    public function testBuildLargeListAndInsertRandomValues(): void
    {
        if (getenv('RUN_PERF_TEST') !== '1') {
            self::markTestSkipped('Set RUN_PERF_TEST=1 to run performance test.');
        }

        $n = 1000000;
        $gap = 2;
        $insertCount = 100;

        $toInsert = [];
        while (count($toInsert) < $insertCount) {
            $candidate = random_int(0, ($n * $gap) - 1);
            if (($candidate % $gap) === 0) {
                continue;
            }
            $toInsert[$candidate] = true;
        }
        $toInsert = array_keys($toInsert);

        $run = function (bool $fingerEnabled) use ($n, $gap, $toInsert, $insertCount): void {
            $t0 = hrtime(true);
            $list = SortedLinkedList::fromSortedUnique((function () use ($n, $gap): iterable {
                for ($i = 0; $i < $n; $i++) {
                    yield $i * $gap;
                }
            })());
            $t1 = hrtime(true);

            $list->enableFinger($fingerEnabled);

            self::assertSame($n, $list->count());

            $buildMs = ($t1 - $t0) / $n;
            fwrite(STDERR, sprintf(
                "\n[perf] finger=%s build %d items: %.2f ms\n",
                $fingerEnabled ? 'on' : 'off',
                $n,
                $buildMs,
            ));
            fwrite(STDERR, sprintf(
                "[perf] finger=%s peak(true): %.2f MiB\n",
                $fingerEnabled ? 'on' : 'off',
                memory_get_peak_usage(true) / 1024 / 1024,
            ));

            $t2 = hrtime(true);
            foreach ($toInsert as $value) {
                $list->add($value);
            }
            $t3 = hrtime(true);

            self::assertSame($n + $insertCount, $list->count());

            $insertMs = ($t3 - $t2) / $n;
            fwrite(STDERR, sprintf(
                "[perf] finger=%s insert %d items: %.2f ms\n",
                $fingerEnabled ? 'on' : 'off',
                $insertCount,
                $insertMs,
            ));
            fwrite(STDERR, sprintf(
                "[perf] finger=%s memory after inserts (true): %.2f MiB\n",
                $fingerEnabled ? 'on' : 'off',
                memory_get_usage(true) / 1024 / 1024,
            ));

            $list->clear();
            unset($list);
            gc_collect_cycles();
        };

        $run(false);
        $run(true);

        $toInsertStrings = [];
        while (count($toInsertStrings) < $insertCount) {
            $candidate = random_int(0, ($n * $gap) - 1);
            if (($candidate % $gap) === 0) {
                continue;
            }
            // ensure uniqueness
            $toInsertStrings[$candidate] = true;
        }
        $toInsertStrings = array_keys($toInsertStrings);

        $runStrings = function (bool $fingerEnabled) use ($n, $gap, $toInsertStrings, $insertCount): void {
            $t0 = hrtime(true);
            $list = SortedLinkedList::fromSortedUnique((function () use ($n, $gap): iterable {
                for ($i = 0; $i < $n; $i++) {
                    yield sprintf('v%08d', $i * $gap);
                }
            })());
            $t1 = hrtime(true);

            $list->enableFinger($fingerEnabled);

            self::assertSame($n, $list->count());

            $buildMs = ($t1 - $t0) / 1_000_000;
            fwrite(STDERR, sprintf(
                "\n[perf:str] finger=%s build %d items: %.2f ms\n",
                $fingerEnabled ? 'on' : 'off',
                $n,
                $buildMs,
            ));
            fwrite(STDERR, sprintf(
                "[perf:str] finger=%s peak(true): %.2f MiB\n",
                $fingerEnabled ? 'on' : 'off',
                memory_get_peak_usage(true) / 1024 / 1024,
            ));

            $t2 = hrtime(true);
            foreach ($toInsertStrings as $value) {
                // candidate values were chosen to be odd; they won't exist in the even-only base list.
                $list->add(sprintf('v%08d', $value));
            }
            $t3 = hrtime(true);

            self::assertSame($n + $insertCount, $list->count());

            $insertMs = ($t3 - $t2) / $n;
            fwrite(STDERR, sprintf(
                "[perf:str] finger=%s insert %d items: %.2f ms\n",
                $fingerEnabled ? 'on' : 'off',
                $insertCount,
                $insertMs,
            ));
            fwrite(STDERR, sprintf(
                "[perf:str] finger=%s memory after inserts (true): %.2f MiB\n",
                $fingerEnabled ? 'on' : 'off',
                memory_get_usage(true) / 1024 / 1024,
            ));

            $list->clear();
            unset($list);
            gc_collect_cycles();
        };

        $runStrings(false);
        $runStrings(true);
    }
}
