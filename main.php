<?php

declare(strict_types=1);

/**
 * THE MISCHIEVOUS MAIL GOBLIN
 *
 * A mischievous goblin has taken control of your SaaS email scheduler.
 * Fortunately, it still follows a few rules.
 *
 * TODO:
 *
 * Implement scheduleJobs().
 *
 * Organise the outgoing email jobs into minute-by-minute batches. Return a
 * list of minutes (minute 1 first), where each minute is the list of job ids
 * sent in that minute, in priority order, e.g. [['A', 'D'], ['B', 'C'], ['E']].
 *
 * Rules:
 * 1. Send no more than $maxPerMinute jobs per minute.
 * 2. Each mailbox can send at most one job per minute.
 * 3. Prioritise higher-priority jobs first. If a job's mailbox has already
 *    sent this minute, the job waits for a later minute and the next job in
 *    line takes the free slot.
 * 4. When priorities are equal, preserve their original input order.
 * 5. Every job must eventually be scheduled exactly once.
 *
 * Assumptions:
 * - Every job has the three keys below; you do not need to validate input.
 * - Job ids are unique.
 * - $maxPerMinute is at least 1.
 *
 * Job shape:
 *   [
 *       'id'       => 'A',     // string, unique
 *       'mailbox'  => 'red',   // string
 *       'priority' => 3,       // int, higher = more urgent
 *   ]
 *
 * Use PHP 8+, no frameworks or packages. Helper functions are welcome.
 *
 * Be ready to explain the time complexity of your solution.
 */

/**
 * @param array<int, array{id: string, mailbox: string, priority: int}> $jobs
 * @return array<int, array<int, string>> one list of job ids per minute
 */
function scheduleJobs(array $jobs, int $maxPerMinute): array
{
    // TODO: implement

    return [];
}

// -------------------------------------------------------------------------
// Sample data
// -------------------------------------------------------------------------

$jobs = [
    ['id' => 'A', 'mailbox' => 'red',   'priority' => 3],
    ['id' => 'B', 'mailbox' => 'blue',  'priority' => 2],
    ['id' => 'C', 'mailbox' => 'red',   'priority' => 2],
    ['id' => 'D', 'mailbox' => 'green', 'priority' => 3],
    ['id' => 'E', 'mailbox' => 'blue',  'priority' => 1],
];

// Expected return value of scheduleJobs($jobs, 2):
//
// [
//     ['A', 'D'],  // Minute 1
//     ['B', 'C'],  // Minute 2
//     ['E'],       // Minute 3
// ]

$result = scheduleJobs($jobs, 2);

foreach ($result as $index => $minute) {
    echo 'Minute ' . ($index + 1) . ': ' . implode(', ', $minute) . "\n";
}
