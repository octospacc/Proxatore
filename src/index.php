<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

$startTime = hrtime(true);

require 'platforms.php';
require 'templates.php';
require 'config.php';
require 'utils.php';

$searchResults = $finalData = $errorMessage = null;
$path = lstrip($_SERVER['REQUEST_URI'], SCRIPT_NAME, 1);

if ($search = readProxatoreParam('search')) {
    if ($url = parseAbsoluteUrl($search)) {
        return redirectTo($url);
    } else {
        $searchResults = searchHistory($search);
    }
} else if ($group = readProxatoreParam('group')) {
    $searchResults = [];
    foreach (json_decode($group) as $path) {
        $segments = explode('/', trim($path, '/'));
        $platform = array_shift($segments);
        $relativeUrl = implode('/', $segments);
        $data = getPageData($platform, $relativeUrl);
        $searchResults[] = $data['result'];
    }
} else {
    $path = trim($path, '/');
    if ($url = parseAbsoluteUrl($path)) {
        return redirectTo($url);
    }

    $segments = explode('/', $path);
    $platform = null;
    $upstream = $segments[0] ?? null;
    $relativeUrl = implode('/', array_slice($segments, 1));

    if ($upstream === $_SERVER['HTTP_HOST']) {
        return redirectTo($relativeUrl);
    }

    // after refactoring this now treats aliases as canonical, we should decide on the matter
    if (str_starts_with($upstream, '__') && str_ends_with($upstream, '__')) {
        return handleApiRequest($segments);
    } else if (isExactPlatformName($upstream)) {
        $platform = $upstream;
    } else if ($platform = platformFromUpstream($upstream)) {
        return redirectTo(makeInternalBareUrl($platform, $relativeUrl));
    }

    if (!$platform && isset(PLATFORMS_REDIRECTS[$upstream])) {
        return redirectTo(getPlatformRedirectionUrl($upstream, $relativeUrl));
    } else if (!$platform) {
        foreach (PLATFORMS_USERSITES as $domain) {
            if (str_ends_with($upstream, ".{$domain}")) {
                $platform = $upstream;
                break;
            }
        }
    }

    if ($upstream && !$platform) {
        $errorMessage = "Upstream <code>{$upstream}</code> not supported!";
    } else if ($finalData = getPageData($platform, $relativeUrl)) {
        http_response_code($finalData['code']);
        fetchPageMedia($finalData);
        if ($finalData['code'] >= 400) {
            $searchResults = searchExactHistory($platform, $finalData['result']['relativeurl']);
            if (sizeof($searchResults)) {
                $finalData['result'] = $searchResults[0];
            }
        } else if (readProxatoreBool('history')) {
            saveHistory($finalData['result']);
        }
        if (readProxatoreBool('relativemedia')) {
            $count = 0;
            foreach (['video', 'image'] as $type) {
                if ($finalData['result'][$type]) {
                    $finalData['result'][$type] = SCRIPT_NAME . "__media__/{$platform}/{$finalData['result']['relativeurl']}/{$count}";
                    $count++;
                }
            }
        }
        $searchResults = [$finalData['result']];
    } else if ($path) {
        http_response_code(404);
    }
}

$output = [
    'title' => $finalData['result']['title'],
    'description' => htmlspecialchars($finalData['result']['description'] ?? ucfirst(APP_DESCRIPTION)),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= htmlspecialchars(APP_NAME . ($output['title'] ? " - {$output['title']}" : '')) ?></title>
<meta name="description" content="<?= $output['description'] ?>" />
<meta name="twitter:title" property="og:title" content="<?= htmlspecialchars($output['title'] ?? APP_NAME) ?>" />
<meta name="twitter:description" property="og:description" content="<?= $output['description'] ?>" />
<!-- <meta property="og:locale" content="<?= htmlspecialchars($finalData['result']['locale'] ?? '') ?>" /> -->
<meta property="og:type" content="<?= htmlspecialchars($finalData['result']['type'] ?? '') ?>" />
<?php if ($image = $finalData['result']['image'] ?? null): ?>
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:image" property="og:image" content="<?= htmlspecialchars($image) ?>" />
<?php endif; ?>
<?php if ($video = $finalData['result']['video'] ?? null): ?>
    <meta property="og:video" content="<?= htmlspecialchars($video) ?>" />
    <meta property="og:video:type" content="<?= htmlspecialchars($finalData['result']['videotype'] ?: 'video/mp4') ?>" />
<?php endif; ?>
<?php if ($audio = $finalData['result']['audio'] ?? null): ?>
    <meta property="og:audio" content="<?= htmlspecialchars($audio) ?>" />
    <meta property="og:audio:type" content="audio/mpeg" />
<?php endif; ?>
<meta property="og:site_name" content="<?= APP_NAME . ' ' . ($finalData['result']['platform'] ?? '') ?>" />
<?php if ($result = $finalData['result'] ?? null): ?>
    <meta property="og:url" content="<?= htmlspecialchars(makeCanonicalItemUrl($result)) ?>" />
    <link rel="canonical" href="<?= htmlspecialchars(makeCanonicalItemUrl($result)) ?>" />
<?php else: ?>
    <meta property="og:url" content="<?= htmlspecialchars(makeSelfUrl()) ?>" />
    <link rel="canonical" href="<?= htmlspecialchars(makeSelfUrl()) ?>" />
<?php endif; ?>
<!-- <link rel="alternate" type="application/json+oembed" href="" />
<link rel="alternate" type="application/xml+oembed" href="" /> -->
<meta name="google-site-verification" content="<?= GOOGLE_VERIFICATION ?>" />
<meta name="msvalidate.01" content="<?= BING_VERIFICATION ?>" />
<style><?php require 'style.css'; ?></style>
</head>
<body class="<?= readProxatoreParam('viewmode'); ?>">
<div class="container">
<?php if (readProxatoreParam('viewmode') !== 'embed'): ?>
    <h1><a href="<?= SCRIPT_NAME ?>"><?= APP_NAME; ?></a></h1>
    <p style="text-align: center;"><?= $errorMessage ?></p>
    <form method="GET" action="<?= SCRIPT_NAME ?>">
        <div class="search-bar">
            <input type="text" required="required" name="proxatore-search" placeholder="Search or Input URL"
                value="<?= htmlspecialchars(readProxatoreParam('search') ?? makeCanonicalItemUrl($finalData['result'] ?? null) ?: ($group = readProxatoreParam('group') ? makeSelfUrl('?proxatore-group=' . urlencode($group)) : '')) ?>" />
            <button type="submit">Go 💣️</button>
        </div>
        <details style="margin-bottom: 20px;">
            <summary>Options</summary>
            <ul>
                <li><label><input type="checkbox" name="proxatore-history" value="false" <?php if (!readProxatoreBool('history')) echo 'checked="checked"' ?> /> Incognito Mode (don't save query to global cache/history)</label></li>
            </ul>
        </details>
    </form>
<?php endif; ?>
<?php if (!isset($searchResults)) {
    $platforms = '';
    $searchPrefix = (SCRIPT_NAME . '?proxatore-search=');
    echo '<p>Supported Platforms:</p><ul class="platforms">';
    foreach (array_keys(PLATFORMS) as $platform) {
        $platforms .= ((isset(PLATFORMS_ALIASES[$platform])) ? '/' : "</a></li><li><a href='{$searchPrefix}\"platform\":\"{$platform}\"'>") . $platform;
    }
    foreach (PLATFORMS_USERSITES as $platform) {
        $platforms .= "</a></li><li><a href='{$searchPrefix}.{$platform}\",\"relativeurl\"'>*.{$platform}";
    }
    echo substr($platforms, strlen('</a></li>')) . '</a></li></ul>';
    // echo '<details><summary>Query string API</summary><ul>
    //     <li>/?<code>proxatore-search=</code>{search term} — Make a full-text search or load a given URL</li>
    //     <li>...?<code>proxatore-history=</code>{true,false} — Specify if a given query must be stored in the global search history (default: true)</li>
    // </ul></details>';
    echo '<details><summary>Help & Info</summary>
        <h3>What is this?</h3><p>
            '.APP_NAME.' is '.APP_DESCRIPTION.'
            <br />It allows you to bypass ratelimits and georestrictions when accessing contents from many specific Internet platforms,
            and to view them with a clean and streamlined interface, that works well on both modern systems and old browsers or slow connections.
            <br />Additionally, it allows you to share links between social media platforms, ensuring link previews, which are often blocked by competitors, always display correctly.
        </p>
    </details>';
    echo '<p>
        Made with 🕸️ and 🧨 by <a href="https://hub.octt.eu.org">OctoSpacc</a>.
        <br />
        <small>
            Licensed under <a href="https://www.gnu.org/licenses/agpl-3.0.html" target="_blank">AGPLv3</a>.
            Source Code & Info: <a href="https://gitlab.com/octospacc/Proxatore">Official Repository</a>' . (MODIFIED_SOURCE_CODE ? ', <a href="' . MODIFIED_SOURCE_CODE . '">Modified Source Code</a>.</small>' : '.') . '
        </small>
        <!-- <span style="float: right;padding: 8px;">Proxatore v.########</span> -->
    </p>';
} ?>
<?php if (($finalData ?? null) && readProxatoreBool('embedfirst') && readProxatoreParam('viewmode') !== 'embed' /* && !inPlatformArray($finalData['platform'], PLATFORMS_NOEMBED) */) iframeHtml($finalData); ?>
<?php if (isset($searchResults)): ?>
    <?php if (!isset($finalData['result'])): ?>
        <h3>Search results:</h3>
        <?php if (!sizeof($searchResults)): ?>
            <p>Nothing was found.</p>
        <?php endif; ?>
    <?php endif; ?>
    <?php foreach ($searchResults as $item): ?>
        <?= historyItemHtml($item, isset($finalData['result'])) ?>
    <?php endforeach; ?>
<?php endif; ?>
<p style="text-align: right;">Issues? Switch to a <a href="<?= SCRIPT_NAME; ?>__randominstance__/<?= $path; ?>">random instance</a>.</p>
<?php if (($finalData ?? null) && !readProxatoreBool('embedfirst') && readProxatoreParam('viewmode') !== 'embed' /* && !inPlatformArray($finalData['platform'], PLATFORMS_NOEMBED) */) iframeHtml($finalData); ?>
</div>
<script><?php require 'script.js'; ?></script>
<!-- Page rendered in <?= (hrtime(true) - $startTime)/1e+6 ?> ms -->
</body>
</html>
