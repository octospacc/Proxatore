<?php
const APPNAME = 'Proxatore';

const PLATFORMS = [
	'facebook' => ['facebook.com', 'm.facebook.com'],
	'instagram' => ['instagram.com', 'ddinstagram.com', 'd.ddinstagram.com'],
	'reddit' => ['old.reddit.com', 'reddit.com'],
	'telegram' => ['t.me', 'telegram.me'],
	'tiktok' => ['tiktok.com', 'vxtiktok.com'],
	'twitter' => ['twitter.com', 'fxtwitter.com', 'vxtwitter.com'],
	'x' => ['x.com', 'fixupx.com'],
	//'wordpress' => ['wordpress.com'],
];

const EMBEDS = [
	'reddit' => ['embed.reddit.com'],
];

const EMBEDS_SUFFIXES = [
	'instagram' => '/embed/captioned/',
	'telegram' => '?embed=1&mode=tme',
];

define('HISTORY_FILE', './' . $_SERVER['SCRIPT_NAME'] . '.history.jsonl');

function lstrip($str, $sub) {
    return implode($sub, array_slice(explode($sub, $str), 1));
}

function fetchContent($url) {
    $ch = curl_init();
    //$useragent = 'Mozilla/5.0 (X11; Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0';
    $useragent = 'curl/' . curl_version()['version'];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    http_response_code();
    curl_close($ch);
    return [
        'body' => $body,
        'code' => $code,
    ];
}

function makeEmbedUrl($platform, $relativeUrl) {
	return 'https://' . (EMBEDS[$platform][0] ?: PLATFORMS[$platform][0] ?: '') . '/' . $relativeUrl . (EMBEDS_SUFFIXES[$platform] ?? '');
}

function parseMetaTags($doc) {
    $metaTags = [];
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->hasAttribute('name') || $meta->hasAttribute('property')) {
            $metaTags[$meta->getAttribute('name') ?: $meta->getAttribute('property')] = $meta->getAttribute('content');
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

if (isset($_GET['search']) && ($search = $_GET['search']) !== '') {
    if (str_starts_with(strtolower($search), 'https://')) {
        header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/' . lstrip($search, 'https://'));
        die();
    }
    $searchResults = searchHistory($search);
} else {
    $segments = explode('/', trim($path, '/'));
    array_shift($segments);

    $platform = null;
    $upstreamUrl = null;

    $upstream = $segments[0] ?? null;
    $relativeUrl = implode('/', array_slice($segments, 1));

    if (isset(PLATFORMS[$upstream])) {
        $platform = $upstream;
        $domain = PLATFORMS[$upstream][0];
        $upstreamUrl = "https://$domain";
    } else {
        foreach ([PLATFORMS, EMBEDS] as $array) {
            foreach ($array as $platform => $domains) {
                if (in_array($upstream, $domains) || in_array(lstrip($upstream, 'www.'), $domains)) {
                    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/' . $platform . '/' . $relativeUrl);
                    die();
                    //$upstreamUrl = "https://$upstream";
                    //break;
                }
            }
        }
    }

    if ($relativeUrl && $upstreamUrl && ($content = fetchContent("$upstreamUrl/$relativeUrl"))['body']) {
        $doc = new DOMDocument();
        $doc->loadHTML($content['body']);
        $metaTags = parseMetaTags($doc);
        $immediateResult = [
            'platform' => $platform,
            'relativeurl' => $relativeUrl,
            //'datetime' => date('Y-m-d H:i:s'),
            //'request_time' => time(),
            'type' => $metaTags['og:type'] ?? '',
            'image' => $metaTags['og:image'] ?? '',
            'video' => $metaTags['og:video'] ?? '',
            'title' => $metaTags['og:title'] ?? '',
            'author' => $metaTags['og:site_name'] ?? '',
            'description' => $metaTags['og:description'] ?: $metaTags['description'] ?: '',
        ];
        if (!$immediateResult['video']) {
            $html = fetchContent(makeEmbedUrl($platform, $relativeUrl))['body'];
            $vidpos = strpos($html, '.mp4');
            if ($vidpos) {
                $startpos = strpos(substr(strrev($html), 0, $vidpos), '"');
                $endpos = strpos(substr($html, $vidpos), '"');
                echo $startpos . '|' . $endpos; //substr($html, $startpos, $endpos);
            }
        }
        $searchResults = [$immediateResult];
        //if ($immediateResult['title'] || $immediateResult['description']) {
        //    saveHistory($immediateResult);
        //} else 
        if ($content['code'] >= 400) {
            $searchResults = searchHistory($relativeUrl);
            $immediateResult = $searchResults[0];
        } else {
            saveHistory($immediateResult);
        }
    } else {
        http_response_code(404);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo APPNAME; ?></title>
    <meta name="description" content="<?= htmlspecialchars($immediateResult['description'] ?? 'Content Proxy for viewing media and text from various platforms.') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($immediateResult['title'] ?? 'Content Proxy') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($immediateResult['description'] ?? 'View content from supported platforms.') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($immediateResult['type'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($immediateResult['image'] ?? '') ?>">
    <meta property="og:video" content="<?= htmlspecialchars($immediateResult['video'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars("https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>">
    <link rel="canonical" href="https://<?= htmlspecialchars(PLATFORMS[$immediateResult['platform']][0] ?? '') ?>/<?= htmlspecialchars($immediateResult['relativeurl']) ?>">
<!--    <style>
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
            padding: 1em 0;
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
        .history-item p {
            
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
    </style> -->
<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Roboto', Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #f0f2f5;
        color: #1c1e21;
    }
    
    iframe {
        width: 100%;
        height: 50vh;
        border: none;
    }

    .container {
        max-width: 900px;
        width: 90%;
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    a.button {
        padding: 0.5em;
        border: 1px solid gray;
        border-radius: 8px;
        text-decoration: none;
        margin: 0.5em;
        display: block;
    }

    h1, h1 a {
        text-align: center;
        margin-bottom: 20px;
        font-size: 2rem;
        color: #1877f2;
        text-decoration: none;
    }

    h2 {
        font-size: 1.5rem;
        margin-top: 20px;
        color: #444;
        border-bottom: 2px solid #1877f2;
        padding-bottom: 5px;
    }

    .history-item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #e6e6e6;
        padding: 15px 0;
        transition: background-color 0.3s;
    }

    .history-item:hover {
        background-color: #f9f9f9;
    }

    .history-item img {
        /*width: 49%;
        max-width: 49%;*/
        width: 100%;
        max-width: 100%;
        /* max-width: 100px;
        max-height: 100px; */
        margin-right: 15px;
        border-radius: 4px;
        object-fit: cover;
    }

    .history-item div {
        display: flex;
        flex-direction: column;
        justify-content: center;
        max-width: 49%;
        /*padding: 1em;*/
    }
    
    img, video {
        padding: 1em;
    }
    
    img[src=""], video[src=""] {
        display: none;
    }

    .history-item strong {
        font-size: 1.2rem;
        color: #1c1e21;
        margin-bottom: 5px;
        display: -webkit-box;
        <?php
            similar_text($immediateResult['title'], $immediateResult['description'], $percent);
            if ($percent > 90): ?>
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        overflow: hidden;
        <?php endif; ?>
    }

    .history-item small {
        font-size: 0.9rem;
        color: #606770;
    }

    .search-bar {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }

    .search-bar input {
        flex: 1;
        max-width: 600px;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 1rem;
        transition: box-shadow 0.3s, border-color 0.3s;
    }

    .search-bar input:focus {
        border-color: #1877f2;
        box-shadow: 0 0 5px rgba(24, 119, 242, 0.5);
        outline: none;
    }

    .search-bar button {
        margin-left: 10px;
        padding: 10px 20px;
        background-color: #1877f2;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .search-bar button:hover {
        background-color: #155dbb;
    }

    @media (max-width: 600px) {
        .search-bar input {
            width: 100%;
            margin-bottom: 10px;
        }

        .search-bar {
            flex-direction: column;
        }

        .search-bar button {
            width: 100%;
            margin: 0;
        }

        .history-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .history-item img {
            margin-bottom: 10px;
            max-width: 100%;
        }
        
        .history-item div {
            max-width: 100%;
        }
    }
</style>

</head>
<body>
    <div class="container">
        <h1><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>"><?php echo APPNAME; ?></a></h1>
        <form class="search-bar" method="get" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME'] . '/') ?>">
            <input type="text" required="required" name="search" placeholder="Search or Input URL" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Go 💣️</button>
        </form>

        <?php if (isset($searchResults)): ?>
            <?php foreach ($searchResults as $item): ?>
                <div class="history-item">
                    <div>
                        <img src="<?= htmlspecialchars($item['image'] ?? '') ?>">
                        <video src="<?= htmlspecialchars($item['video'] ?? '') ?>" controls="controls"></video>
                    </div>
                    <div>
                        <p>
                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                            <small><?= htmlspecialchars($item['platform']) ?><!-- | <?= htmlspecialchars($item['datetime']) ?>--></small>
                        </p>
                        <p style="white-space: preserve-breaks; border-left: 2px solid black; padding: 1em; word-break: break-word;"><?= htmlspecialchars($item['description']) ?></p>
                        <p>
                            <a class="button" href="https://<?= htmlspecialchars(PLATFORMS[$item['platform']][0] ?? '') ?>/<?= htmlspecialchars($item['relativeurl']) ?>" target="_blank">Open Original on <code><?= htmlspecialchars(PLATFORMS[$item['platform']][0] ?? '') ?></code></a>
                            <a class="button" href="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'] . '/' . $item['platform'] . '/' . $item['relativeurl']) ?>">Proxatore Permalink</a>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (isset($immediateResult)): ?>
            <iframe src="<?= htmlspecialchars(makeEmbedUrl($immediateResult['platform'], $immediateResult['relativeurl'])) ?>"></iframe>
        <?php endif; ?>

    </div>
</body>
</html>

