<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

// require 'vendor/OcttDb/index.php';

function loadHistory(): array {
    $history = [];
    if (file_exists(HISTORY_FILE)) {
        $lines = file(HISTORY_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($item = json_decode($line, true)) {
                $history[] = $item;
            }
        }
    }
    return $history;
}

function saveHistory(array $entry): void {
    if (inPlatformArray($entry['platform'], PLATFORMS_FAKE404)) {
        $history = searchExactHistory($entry['platform'], implode('/', array_slice(explode('/', $entry['relativeurl']), -1)));
        if (sizeof($history)) {
            unset($history[0]['relativeurl']);
            unset($entry['relativeurl']);
            if (json_encode($history[0], JSON_UNESCAPED_SLASHES) === json_encode($entry, JSON_UNESCAPED_SLASHES)) {
                return;
            } else {
                // TODO update cache of main page
            }
        } else {
            // TODO update cache of main page
        }
    }
    $history = loadHistory();
    $history = array_filter($history, function ($item) use ($entry) {
        return (($item['platform'] !== $entry['platform']) ||
                ($item['relativeurl'] !== $entry['relativeurl']));
    });
    $history[] = $entry;
    $lines = array_map(fn($item) => json_encode($item, JSON_UNESCAPED_SLASHES), $history);
    file_put_contents(HISTORY_FILE, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
}

function searchHistory(string $query): array {
    $results = $fake404 = [];
    foreach (loadHistory() as $entry) {
        if (stripos(json_encode($entry, JSON_UNESCAPED_SLASHES), $query) !== false) {
            if (inPlatformArray($entry['platform'], PLATFORMS_FAKE404)) {
                $entry2 = $entry;
                unset($entry2['relativeurl']);
                foreach ($fake404 as $item) {
                    if (json_encode($entry2, JSON_UNESCAPED_SLASHES) === json_encode($item, JSON_UNESCAPED_SLASHES)) {
                        goto skip;
                    }
                }
                $fake404[] = $entry2;
            }
            $results[] = $entry;
            skip:
        }
    }
    return $results;
}

function searchExactHistory(string $platform, string $relativeUrl): array {
    return searchHistory(json_encode([
        'platform' => $platform,
        'relativeurl' => $relativeUrl,
    ], JSON_UNESCAPED_SLASHES));
}
