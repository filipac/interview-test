<?php

declare(strict_types=1);

/**
 * Tiny test harness for main.php.
 *
 * Run: php tests.php (the "Run unit tests" button on Coderbyte)
 *
 * Uses scheduleJobs() and the sample $jobs defined in main.php.
 * Feel free to add your own cases.
 */

// Load main.php without echoing its sample output.
ob_start();
require __DIR__ . '/main.php';
ob_end_clean();

$passed = 0;
$failed = 0;

function job(string $id, string $mailbox, int $priority): array
{
    return ['id' => $id, 'mailbox' => $mailbox, 'priority' => $priority];
}

function check(string $name, array $jobs, int $maxPerMinute, array $expected): void
{
    global $passed, $failed;

    $actual = scheduleJobs($jobs, $maxPerMinute);

    if ($actual === $expected) {
        $passed++;
        echo "✅ PASS  {$name}\n";
        return;
    }

    $failed++;
    echo "❌ FAIL  {$name}\n";
    echo '         expected: ' . json_encode($expected) . "\n";
    echo '         actual:   ' . json_encode($actual) . "\n";
}

/** Checks rules 1, 2 and 5 on any schedule (does not check priority order). */
function breaksAnyRule(array $jobs, int $maxPerMinute, array $schedule): ?string
{
    $mailboxOf = array_column($jobs, 'mailbox', 'id');
    $seen = [];

    foreach ($schedule as $index => $minute) {
        if (count($minute) > $maxPerMinute) {
            return 'minute ' . ($index + 1) . ' sends more than $maxPerMinute jobs';
        }

        $mailboxes = [];
        foreach ($minute as $id) {
            if (!isset($mailboxOf[$id])) {
                return "unknown job id {$id}";
            }
            if (isset($seen[$id])) {
                return "job {$id} is scheduled twice";
            }
            $seen[$id] = true;
            $mailboxes[] = $mailboxOf[$id];
        }

        if (count($mailboxes) !== count(array_unique($mailboxes))) {
            return 'minute ' . ($index + 1) . ' sends twice from one mailbox';
        }
    }

    return count($seen) === count($jobs) ? null : 'some jobs were never scheduled';
}

check('sample dataset', $jobs, 2, [['A', 'D'], ['B', 'C'], ['E']]);

check('empty input', [], 2, []);

check('single job', [job('A', 'red', 1)], 3, [['A']]);

check('maxPerMinute = 1 sends one job per minute in priority order', $jobs, 1, [
    ['A'], ['D'], ['B'], ['C'], ['E'],
]);

check('one mailbox sends at most once per minute', [
    job('A', 'red', 1),
    job('B', 'red', 3),
    job('C', 'red', 2),
], 3, [['B'], ['C'], ['A']]);

check('busy mailbox: the next job takes the free slot', [
    job('A', 'red', 3),
    job('B', 'red', 2),
    job('C', 'blue', 1),
], 2, [['A', 'C'], ['B']]);

check('a waiting job keeps its place in line', [
    job('A', 'red', 5),
    job('B', 'red', 4),
    job('C', 'blue', 3),
    job('D', 'green', 2),
    job('E', 'yellow', 1),
], 2, [['A', 'C'], ['B', 'D'], ['E']]);

check('equal priorities keep input order (not id order)', [
    job('Z', 'red', 1),
    job('M', 'blue', 1),
    job('A', 'green', 1),
], 2, [['Z', 'M'], ['A']]);

check('equal priorities in one mailbox keep input order', [
    job('R2', 'red', 1),
    job('R1', 'red', 1),
], 2, [['R2'], ['R1']]);

check('limit above job count: everything in minute 1', [
    job('C', 'blue', 1),
    job('A', 'red', 3),
    job('B', 'green', 2),
], 10, [['A', 'B', 'C']]);

check('fewer mailboxes than the limit', [
    job('A', 'red', 3),
    job('B', 'blue', 2),
    job('C', 'red', 1),
    job('D', 'blue', 1),
], 5, [['A', 'B'], ['C', 'D']]);

check('priority wins over using fewer minutes', [
    job('X', 'blue', 5),
    job('Y', 'green', 4),
    job('R1', 'red', 1),
    job('R2', 'red', 1),
], 2, [['X', 'Y'], ['R1'], ['R2']]);

// 500 random jobs: no exact expected value, so check rules 1, 2 and 5 only.
mt_srand(42);
$randomJobs = [];
for ($i = 0; $i < 500; $i++) {
    $randomJobs[] = job("J{$i}", 'mailbox-' . mt_rand(1, 7), mt_rand(1, 5));
}

$problem = breaksAnyRule($randomJobs, 3, scheduleJobs($randomJobs, 3));

if ($problem === null) {
    $passed++;
    echo "✅ PASS  500 random jobs keep rules 1, 2 and 5\n";
} else {
    $failed++;
    echo "❌ FAIL  500 random jobs keep rules 1, 2 and 5: {$problem}\n";
}

echo "\n✅ {$passed} passed, ❌ {$failed} failed\n";

if ($failed === 0) {
    echo "🎉 All tests passed!\n";
}

exit($failed === 0 ? 0 : 1);
