<?php

const PROXATORE_URL = 'https://proxatore.octt.eu.org/'; // getenv('PROXY_BASE_URL') ?: 'http://localhost';

const SEARCH_HEADING = '<h3>Search results:</h3>';

$tests = [
    [
        'name' => 'Search with "test" query',
        'path' => '?proxatore-search=test',
        'expectedContains' => [SEARCH_HEADING],
    ],
    [
        'name' => 'Search with inexistent term',
        'path' => '?proxatore-search=ThisStringSaying'.uniqid('', true).'DoesNeverExist',
        'expectedContains' => [SEARCH_HEADING, '<p>Nothing was found.</p>'],
    ],
    // [
    //     'name' => 'Invalid platform',
    //     'path' => 'unknown/path',
    //     'expectedContains' => ['No immediate results or search query provided.'],
    // ],
];

$linkTests = [
    [
        'name' => 'YouTube 1',
        'title' => 'Rick Astley - Never Gonna Give You Up (Official Music Video)',
        'path' => 'youtube.com/watch?v=dQw4w9WgXcQ',
    ],
    [
        'name' => 'YouTube 2',
        'title' => 'Me at the zoo',
        'path' => 'youtube.com/watch?v=jNQXAC9IVRw',
    ],
    [
        'name' => 'Pinterest 1',
        'path' => 'pinterest.com/pin/305118943524103084',
    ],
    [
        'name' => 'Telegram Image 1',
        'title' => 'Pavel Durov',
        'path' => 'telegram.me/durov/6',
    ],
    [
        'name' => 'Telegram Image+Text 1',
        'path' => 'telegram.me/SpaccInc/7',
        'expectedContains' => ['Simple spacc from yesterday, my USB wall charger is definitely close to breaking completely.'],
    ],
];

foreach ($linkTests as $test) {
    $path = explode('/', ltrim($test['path'], '/'));
    $platform = implode('.', array_slice(explode('.', reset($path)), 0, -1));
    $realPath = "{$platform}/" . implode('/', array_slice($path, 1));
    $tests[] = [ ...$test, 'path' => "http://{$test['path']}", 'expectedRedirect' => $realPath ];
    $tests[] = [ ...$test, 'path' => "https://{$test['path']}", 'expectedRedirect' => $realPath ];
    $tests[] = [ ...$test, 'expectedRedirect' => $realPath ];
    $tests[] = [ ...$test, 'path' => $realPath ];
}

$allPassed = true;

foreach ($tests as $test) {
    if (!isset($test['expectedCode'])) {
        $test['expectedCode'] = (isset($test['expectedRedirect']) ? 302 : 200);
    }

    $url = rtrim(PROXATORE_URL, '/') . '/' . $test['path'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $passed = true;
    $messages = [];

    if ($code !== $test['expectedCode']) {
        $passed = false;
        $messages[] = "Expected HTTP code {$test['expectedCode']}, got $code.";
    }
    if ($code !== 0 && $test['expectedCode'] !== 302) {
        foreach ($test['expectedContains'] ?? (($test['title'] ?? null) ? [$test['title']] : []) as $substr) {
            if (strpos($body, $substr) === false) {
                $passed = false;
                $messages[] = "Response body did not contain '$substr'.";
            }
        }
    } else {
        $messages[] = $error;
    }

    $status = $passed ? '✅ PASS' : '❌ FAIL';
    $name = $test['name'] . (($test['title'] ?? null) ? ": {$test['title']}" : null);
    echo "[{$status}] {$name} <{$test['path']}>\n";
    if (!$passed) {
        foreach ($messages as $msg) {
            echo "    - $msg\n";
        }
        $allPassed = false;
    }
}

exit($allPassed ? 0 : 1);
