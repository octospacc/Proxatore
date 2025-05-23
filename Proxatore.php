<?php
define('HISTORY_FILE', './' . $_SERVER['SCRIPT_NAME'] . '.history.jsonl');

$platforms = [
    'telegram' => ['t.me', 'telegram.me'],
    'reddit' => ['reddit.com', 'old.reddit.com'],
    'instagram' => ['instagram.com'],
    'facebook' => ['facebook.com'],
    'tiktok' => ['tiktok.com'],
    'twitter' => ['twitter.com'],
    'x' => ['x.com'],
];

function fetchContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Content Proxy)');
    $html = curl_exec($ch);
    curl_close($ch);
    return $html ?: null;
}

function parseMetaTags($html) {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $metaTags = [];
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->hasAttribute('property')) {
            $metaTags[$meta->getAttribute('property')] = $meta->getAttribute('content');
        }
    }
    return $metaTags;
}

function loadHistory() {
    $history = [];
    if (file_exists(HISTORY_FILE)) {
        $lines = file(HISTORY_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $history[] = json_decode($line, true);
        }
    }
    return $history;
}

function saveHistory($entry) {
    $history = loadHistory();
    $history = array_filter($history, function ($item) use ($entry) {
        return $item['platform'] !== $entry['platform'] || $item['relativeurl'] !== $entry['relativeurl'];
    });
    $history[] = $entry;
    $lines = array_map(fn($item) => json_encode($item, JSON_UNESCAPED_SLASHES), $history);
    file_put_contents(HISTORY_FILE, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
}

function searchHistory($keyword) {
    $results = [];
    $history = loadHistory();
    foreach ($history as $entry) {
        if (stripos(json_encode($entry, JSON_UNESCAPED_SLASHES), $keyword) !== false) {
            $results[] = $entry;
        }
    }
    return $results;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$immediateResult = null;

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $searchResults = searchHistory($_GET['search']);
} else {
    $segments = explode('/', trim($path, '/'));
    array_shift($segments);

    $upstream = $segments[0] ?? null;
    $relativeUrl = implode('/', array_slice($segments, 1));

    if (isset($platforms[$upstream])) {
        $platform = $upstream;
        $domain = $platforms[$upstream][0];
        $upstreamUrl = "https://$domain/$relativeUrl";
    } else {
        foreach ($platforms as $platform => $domains) {
            if (in_array($upstream, $domains) || in_array(implode('www.', array_slice(explode('www.', $upstream), 1)), $domains)) {
                $upstreamUrl = "https://$upstream/$relativeUrl";
                break;
            }
        }
    }

    if ($relativeUrl && ($html = fetchContent("$upstreamUrl/$relativeUrl"))) {
        $metaTags = parseMetaTags($html);

        $immediateResult = [
            'platform' => $platform,
            'relativeurl' => $relativeUrl,
            'datetime' => date('Y-m-d H:i:s'),
            'request_time' => time(),
            'mediaurls' => [$metaTags['og:image'] ?? null],
            'title' => $metaTags['og:title'] ?? 'No title',
            'author' => $metaTags['og:site_name'] ?? 'Unknown',
            'description' => $metaTags['og:description'] ?? 'No description',
        ];

        saveHistory($immediateResult);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Proxy</title>
    <meta name="description" content="<?= htmlspecialchars($immediateResult['description'] ?? 'Content Proxy for viewing media and text from various platforms.') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($immediateResult['title'] ?? 'Content Proxy') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($immediateResult['description'] ?? 'View content from supported platforms.') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($immediateResult['mediaurls'][0] ?? 'placeholder.jpg') ?>">
    <meta property="og:url" content="<?= htmlspecialchars("https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .history-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-item img {
            /* max-width: 100px; */
            margin-right: 20px;
        }
        .history-item div {
            display: inline-block;
            vertical-align: top;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            width: calc(100% - 100px);
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-bar button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Content Proxy</h1>
        <form class="search-bar" method="get">
            <input type="text" name="search" placeholder="Search history" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
        <?php if ($immediateResult): ?>
            <div class="history-item">
                <img src="<?= htmlspecialchars($immediateResult['mediaurls'][0] ?? 'placeholder.jpg') ?>" alt="Media">
                <div>
                    <strong><?= htmlspecialchars($immediateResult['title']) ?></strong><br>
                    <small>Platform: <?= htmlspecialchars($immediateResult['platform']) ?> | <?= htmlspecialchars($immediateResult['datetime']) ?></small><br>
                    <?= htmlspecialchars($immediateResult['description']) ?><br>
                    <a href="https://<?= htmlspecialchars($platforms[$immediateResult['platform']][0] ?? '') ?>/<?= htmlspecialchars($immediateResult['relativeurl']) ?>" target="_blank">View Original</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($searchResults)): ?>
            <?php foreach ($searchResults as $item): ?>
                <div class="history-item">
                    <img src="<?= htmlspecialchars($item['mediaurls'][0] ?? 'placeholder.jpg') ?>" alt="Media">
                    <div>
                        <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                        <small>Platform: <?= htmlspecialchars($item['platform']) ?> | <?= htmlspecialchars($item['datetime']) ?></small><br>
                        <?= htmlspecialchars($item['description']) ?><br>
                        <a href="https://<?= htmlspecialchars($platforms[$item['platform']][0] ?? '') ?>/<?= htmlspecialchars($item['relativeurl']) ?>" target="_blank">View Original</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

