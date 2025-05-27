<?php
/*
 * Proxatore, a content proxy for viewing and embedding media and text from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>. 
 */

$startTime = hrtime(true);
// require 'vendor/OcttDb/index.php';

/*********** Configuration ***********/

const APP_NAME = '🎭️ Proxatore';
const APP_DESCRIPTION = 'a content proxy for viewing and embedding media and text from various platforms.';

// if you make changes to the source code, please fill this to point to your modified version
const MODIFIED_SOURCE_CODE = '';

// cobalt API server URL; set to false or null or '' to avoid using cobalt
const COBALT_API = 'http://192.168.1.125:9010/';

const OPTIONS_DEFAULTS = [
    'embedfirst' => false,
    'history' => true,
    'htmlmedia' => false,
    'relativemedia' => false,
    'mediaproxy' => false,
    'viewmode' => 'normal',
    //'previewmode' => 'media+summary',
    //'format' => 'html',
];

const GOOGLE_VERIFICATION = 'HjNf-db8xb7lkRNgD3Q8-qeF1lWsbxmCZptRyjLBnrI';
const BING_VERIFICATION = '45DC0FC265FF4059D48677970BE86150';

define('USER_AGENT', "Proxatore/2025/1 ({$_SERVER['SERVER_NAME']})");

/*************************************/

define('SCRIPT_NAME', ($_SERVER['SCRIPT_NAME'] === '/' ? '/' : "{$_SERVER['SCRIPT_NAME']}/"));
define('HISTORY_FILE', './Proxatore.history.jsonl');

// const OPTIONS_OVERRIDES = [
//     'bbs.spacc.eu.org' => [
//         'embedfirst' => true,
//     ],
// ];

const PLATFORMS = [
    'spaccbbs' => ['bbs.spacc.eu.org'],
    'github' => ['github.com'],
    'github-gist' => ['gist.github.com'],
    'bilibili' => ['bilibili.com'],
    'bluesky' => ['bsky.app'],
    'facebook' => ['facebook.com', 'm.facebook.com'],
    'instagram' => ['instagram.com'],
    //'juxt' => ['juxt.pretendo.network'],
    'pinterest' => ['pinterest.com'],
    'raiplay' => ['raiplay.it'],
    'reddit' => ['old.reddit.com', 'reddit.com'],
    'soundcloud' => ['soundcloud.com'],
    'spotify' => ['open.spotify.com'],
    'telegram' => ['t.me', 'telegram.me'],
    'threads' => ['threads.net', 'threads.com'],
    'tiktok' => ['tiktok.com'],
    'twitter' => ['twitter.com'],
    'x' => ['x.com'],
    'xiaohongshu' => ['xiaohongshu.com'],
    'youtube' => ['youtube.com', 'm.youtube.com'],
];

const PLATFORMS_FAKESUBDOMAINS = ['pinterest.com'];

const PLATFORMS_USERSITES = ['altervista.org', 'blogspot.com', 'wordpress.com'];

const PLATFORMS_ALIASES = [
    'x' => 'twitter',
];

const PLATFORMS_SHORTHANDS = [
    'fb' => 'facebook',
    'ig' => 'instagram',
    'tg' => 'telegram',
    'yt' => 'youtube',
];

const PLATFORMS_PROXIES = [
    'bluesky' => ['fxbsky.app'],
    'instagram' => ['ddinstagram.com', 'd.ddinstagram.com', 'kkinstagram.com'],
    'threads' => ['vxthreads.net'],
    'tiktok' => ['vxtiktok.com'],
    'twitter' => ['fxtwitter.com', 'vxtwitter.com', 'fixvx.com'],
    'x' => ['fixupx.com', 'girlcockx.com', 'stupidpenisx.com'],
];

const PLATFORMS_REDIRECTS = [
    'pin.it' => 'pinterest',
    'vm.tiktok.com' => 'tiktok',
    'youtu.be' => 'youtube',
];

const PLATFORMS_API = [
    'github-gist' => [
        'tag' => 'article',
    ],
    'spotify' => [
        'id' => '__NEXT_DATA__',
        'data' => [
            'audio' => "['props']['pageProps']['state']['data']['entity']['audioPreview']['url']",
        ],
    ],
    'tiktok' => [
        'url' => 'https://www.tiktok.com/player/api/v1/items?item_ids=',
        'data' => [
            'description' => "['items'][0]['desc']",
            'video' => "['items'][0]['video_info']['url_list'][0]",    
        ],
    ],
];

const PLATFORMS_COBALT = ['instagram', 'bilibili'];

const PLATFORMS_FAKE404 = ['telegram'];

const PLATFORMS_USEPROXY = ['bluesky', 'twitter', 'x'];

const PLATFORMS_ORDERED = ['telegram'];

// const PLATFORMS_VIDEO = ['youtube', 'bilibili']; // ['facebook', 'instagram'];

const PLATFORMS_WEBVIDEO = ['raiplay'];

const PLATFORMS_NOIMAGES = ['altervista.org', 'wordpress.com'];

const PLATFORMS_PARAMS = [
    'facebook' => true,
    'xiaohongshu' => true,
    'youtube' => ['v'],
];

const EMBEDS_DOMAINS = [
    'spotify' => ['open.spotify.com/embed/'],
    'reddit' => ['embed.reddit.com'],
];

// const EMBEDS_COMPLEX = [
//     'github-gist' => [
//         'prefix' => 'data:text/html;charset=utf-8,<script src="',
//         'suffix' => '.js"></script>',
//     ],
// ];

const EMBEDS_API = [
    'soundcloud' => [
        'meta' => 'twitter:player',
    ],
];

const EMBEDS_PREFIXES_SIMPLE = [
    'tiktok' => 'www.tiktok.com/embed/v3/',
    'twitter' => 'platform.twitter.com/embed/Tweet.html?id=',
];

const EMBEDS_PREFIXES_PARAMS = [
    'youtube' => 'www.youtube.com/embed/[v]',
];

const EMBEDS_SUFFIXES = [
    'github-gist' => '.pibb',
    'instagram' => '/embed/captioned/',
    'telegram' => '?embed=1&mode=tme',
];

define('EMBEDS_PREFIXES_FULL', [
    'facebook' => 'www.facebook.com/plugins/post.php?href=' . urlencode('https://www.facebook.com/'),
]);

function normalizePlatform(string $platform): string {
    if (str_contains($platform, '.')) {
        $platform = lstrip($platform, '.', -2);
    }
    return $platform;
}

function stripWww(string $domain): string|null {
    return (str_starts_with($domain, 'www.') ? lstrip($domain, '.', 1) : null);
}

function isExactPlatformName($platform): bool {
    return isset(PLATFORMS[$platform]);
}

function platformFromAlias(string $alias): string|null {
    $alias = strtolower($alias);
    return (PLATFORMS_ALIASES[$alias] ?? PLATFORMS_SHORTHANDS[$alias] ?? null);
}

function platfromFromDomain(string $upstream): string|null {
    $upstream = strtolower($upstream);
    // check supported domains from most to least likely
    foreach ([PLATFORMS, PLATFORMS_PROXIES, EMBEDS_DOMAINS] as $array) {
        foreach ($array as $platform => $domains) {
            if (in_array($upstream, $domains) || in_array(stripWww($upstream), $domains)) {
                return $platform;
            }
        }
    }
    // check for a known fake subdomain (eg. region-code.example.com)
    foreach (PLATFORMS_FAKESUBDOMAINS as $domain) {
        // currently doesn't handle formats like www.region-code.example.com
        if (lstrip($upstream, '.', 1) === $domain) {
            return platformFromDomain($domain);
        }
    }
    return null; // domain unsupported
}

function platformFromUpstream(string $upstream): string|null {
    return (isExactPlatformName($upstreamLow = strtolower($upstream))
        ? $upstreamLow
        : platformFromAlias($upstream) ?? platfromFromDomain($upstream));
}

function inPlatformArray(string $platform, array $array): bool {
    return in_array(normalizePlatform($platform), $array);
}

function platformMapGet(string $platform, array $array): mixed {
    return $array[normalizePlatform($platform)] ?? null;
}

function lstrip(string $str, string $sub, int $num): string {
    return implode($sub, array_slice(explode($sub, $str), $num));
}

function urlLast(string $url): string {
    $tmp = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
    return end($tmp);
}

function isAbsoluteUrl(string $str): bool {
    $strlow = strtolower($str);
    return (str_starts_with($strlow, 'http://') || str_starts_with($strlow, 'https://'));
}

function parseAbsoluteUrl(string $str): string|null {
    return (isAbsoluteUrl($str)
        ? lstrip($str, '://', 1)
        : null);
}

function makeSelfUrl(string $str=''): string {
    return getRequestProtocol() . '://' . $_SERVER['SERVER_NAME'] . SCRIPT_NAME . $str;
}

function redirectTo(string $url): void {
    if (!($absolute = parseAbsoluteUrl($url)) && !readProxatoreBool('history') /* && !(str_contains($url, '?proxatore-history=false') || str_contains($url, '&proxatore-history=false')) */) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        if (!isset($params['proxatore-history'])) {
            $url = $url . (str_contains($url, '?') ? '&' : '?') . 'proxatore-history=false';
        }
    }
    // if ($_SERVER['REQUEST_METHOD'] === 'GET' || $absolute) {
        header('Location: ' . ($absolute ? '' : SCRIPT_NAME) . $url);
    // } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     echo postRequest(SCRIPT_NAME, 'proxatore-url=' . str_replace('?', '&', $url));
    // }
    die();
}

function getRequestProtocol(): string {
    return $_SERVER['REQUEST_SCHEME'] ?? (($_SERVER['HTTPS'] ?? null) === 'on' ? 'https' : 'http');
}

function fetchContent(string $url, int $redirects=-1): array {
    $ch = curl_init();
    $useragent = 'curl/' . curl_version()['version']; // format the UA like curl CLI otherwise some sites can't behave
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $redirects);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $data = [
        'body' => curl_exec($ch),
        'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'url' => curl_getinfo($ch, CURLINFO_REDIRECT_URL) ?: curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
        // 'error' => curl_error($ch),
    ];
    curl_close($ch);
    return $data;
}

function makeCanonicalUrl(array|null $item): string|null {
    return ($item
        ? ('https://' . (PLATFORMS[$item['platform']][0] ?: $item['platform']) . '/' . $item['relativeurl'])
        : null);
}

function makeEmbedUrl(string $platform, string $relativeUrl, array $meta=null): string {
    $url = null;
    if (isset(EMBEDS_PREFIXES_SIMPLE[$platform])) {
        $url = EMBEDS_PREFIXES_SIMPLE[$platform] . urlLast($relativeUrl);
    } else if (isset(EMBEDS_PREFIXES_PARAMS[$platform])) {
        $url = EMBEDS_PREFIXES_PARAMS[$platform];
        foreach (PLATFORMS_PARAMS[$platform] as $key) {
            parse_str(parse_url($relativeUrl, PHP_URL_QUERY), $params);
            $url = str_replace("[$key]", $params[$key], $url);
        }
    } else if (isset(EMBEDS_PREFIXES_FULL[$platform])) {
        $url = EMBEDS_PREFIXES_FULL[$platform] . urlencode($relativeUrl);
    } else if ($api = (EMBEDS_API[$platform] ?? null)) {
        return $meta[$api['meta']];
    // } else if ($api = EMBEDS_COMPLEX[$platform] ?? null) {
    //     return $api['prefix'] . makeCanonicalUrl(['platform' => $platform, 'relativeurl' => $relativeUrl]) . $api['suffix'];
    } else {
        $url = (EMBEDS_DOMAINS[$platform][0] ?? PLATFORMS[$platform][0] ?? PLATFORMS_PROXIES[$platform][0] ?? $platform) . '/' . trim($relativeUrl, '/') . (EMBEDS_SUFFIXES[$platform] ?? '');
    }
    return "https://{$url}";
}

function makeDataScrapeUrl(string $platform, string $relativeUrl): string {
    return 'https://' . ((inPlatformArray($platform, PLATFORMS_USEPROXY)
        ? (PLATFORMS_PROXIES[$platform][0] ?: PLATFORMS[$platform][0])
        : PLATFORMS[$platform][0]
    ) ?: $platform) . '/' . $relativeUrl;
}

function makeMediaScrapeUrl(array $item): string {
    return /* $embedUrl = */ makeEmbedUrl($item['result']['platform'], $item['result']['relativeurl'], $item['meta']);
    // return (isAbsoluteUrl($embedUrl)
    //     ? $embedUrl
    //     // TODO: if we ever get at this point of the code, then the page has already been scraped and should not do it again for nothing...
    //     : makeDataScrapeUrl($platform, $relativeUrl));
}

function getHtmlAttributes(DOMDocument|string $doc, string $tag, string $attr): array {
    if (is_string($doc)) {
        $doc = htmldom($doc);
    }
    $list = [];
    foreach ($doc->getElementsByTagName($tag) as $el) {
        $list[] = $el->getAttribute($attr);
    }
    return $list;
}

function parseMetaTags(DOMDocument $doc): array {
    $tags = [];
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->hasAttribute('name') || $meta->hasAttribute('property')) {
            $tags[$meta->getAttribute('name') ?: $meta->getAttribute('property')] = $meta->getAttribute('content');
        }
    }
    return $tags;
}

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

function htmldom(string $body): DOMDocument {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    return $doc;
}

function getAnyVideoUrl(string $txt): string|null {
    if ($vidpos = (strpos($txt, '.mp4?') ?? strpos($txt, '.mp4'))) {
        $endpos = strpos($txt, '"', $vidpos);
        $vidstr = substr($txt, 0, $endpos);
        $startpos = $endpos - strpos(strrev($vidstr), '"');
        $vidstr = substr($txt, $startpos, $endpos-$startpos+1);
        $vidstr = html_entity_decode($vidstr);
        $vidstr = json_decode('"' . json_decode('"' . $vidstr . '"')) ?: json_decode('"' . json_decode('"' . $vidstr) . '"');
        return $vidstr;
    } else {
        return null;
    }
}

function makeResultObject(string $platform, string $relativeUrl, array $meta): array {
    $data = [
        'platform' => $platform,
        'relativeurl' => $relativeUrl,
        //'datetime' => date('Y-m-d H:i:s'),
        //'request_time' => time(),
        'locale' => $meta['og:locale'] ?? '',
        'type' => $meta['og:type'] ?? '',
        'image' => $meta['og:image'] ?? '',
        'video' => $meta['og:video'] ?? $meta['og:video:url'] ?? '',
        'videotype' => $meta['og:video:type'] ?? '',
        'htmlvideo' => $meta['og:video'] ?? $meta['og:video:url'] ?? '',
        'audio' => $meta['og:audio'] ?? '',
        'title' => $meta['og:title'] ?? $meta['og:title'] ?? '',
        //'author' => $meta['og:site_name'] ?? '',
        'description' => $meta['og:description'] ?? $meta['description'] ?? '',
        'images' => [],
    ];
    if (inPlatformArray($platform, PLATFORMS_WEBVIDEO) && !$data['video']) {
        $data['video'] = makeCanonicalUrl($data);
        $data['videotype'] = 'text/html';
    }
    if ($data['video'] && $data['videotype'] === 'text/html') {
        $proxy = ((inPlatformArray($platform, PLATFORMS_WEBVIDEO) || readProxatoreBool('mediaproxy') || getQueryArray()['proxatore-mediaproxy'] === 'video') ? 'file' : '');
        $data['htmlvideo'] = SCRIPT_NAME . "__{$proxy}proxy__/{$platform}/{$data['video']}";
        if (readProxatoreBool('htmlmedia')) {
            $data['video'] = $data['htmlvideo'];
            $data['videotype'] = 'video/mp4';
        }
    }
    // } else if (readProxatoreBool('mediaproxy') || getQueryArray()['proxatore-mediaproxy'] === 'video') {
    //     $data['htmlvideo'] = SCRIPT_NAME . "__mediaproxy__/{$platform}/{$data['video']}";
    //     if (readProxatoreBool('htmlmedia')) {
    //         $data['video'] = $data['htmlvideo'];
    //         $data['videotype'] = 'video/mp4';
    //     }
    // }
    return $data;
}

function makeParamsRelativeUrl(string $platform, string $url): string {
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    $url = parse_url($url, PHP_URL_PATH) . '?';
    foreach ($params as $key => $value) {
        if (in_array($key, PLATFORMS_PARAMS[$platform])) {
            $url .= "{$key}={$value}&";
        }
    }
    return rtrim($url, '?&');
}

function getQueryArray(): array {
    // switch ($_SERVER['REQUEST_METHOD']) {
    //     case 'GET':
            return $_GET;
    //     case 'POST':
    //         return $_POST;
    // }
}

function readBoolParam(string $key, bool|null $default=null, array $array=null): bool|null {
    if (!$array) {
        $array = getQueryArray();
    }
    $value = $array[$key] ?? null;
    if ($value && $value !== '') {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    } else {
        return $default;
    }
}

function readProxatoreBool(string $key, array $array=null): bool|null {
    return readBoolParam("proxatore-{$key}", OPTIONS_DEFAULTS[$key], $array);
    // TODO handle domain HTTP referer overrides
}

function readProxatoreParam(string $key, array $array=null): string|null {
    if (!$array) {
        $array = getQueryArray();
    }
    return ($array["proxatore-{$key}"] ?? OPTIONS_DEFAULTS[$key] ?? null);
}

function getPageData($platform, $relativeUrl): array|null {
    if ($platform && $relativeUrl && ($data = fetchContent(makeDataScrapeUrl($platform, $relativeUrl)))['body']) {
        // if (!in_array($platform, PLATFORMS_TRACKING)) {
        //     $relativeUrl = parse_url($relativeUrl, PHP_URL_PATH);
        // }
        if (isset(PLATFORMS_PARAMS[$platform])) {
            if (PLATFORMS_PARAMS[$platform] !== true) {
                $relativeUrl = makeParamsRelativeUrl($platform, $relativeUrl);
            }
        } else {
            $relativeUrl = parse_url($relativeUrl, PHP_URL_PATH);
        }
        $data['doc'] = htmldom($data['body']);
        $data['meta'] = parseMetaTags($data['doc']);
        $data['result'] = makeResultObject($platform, $relativeUrl, $data['meta']);
        return $data;
    } else {
        return null;
    }
}

function getPlatformRedirectionUrl($upstream, $relativeUrl) {
    // TODO: only strip query params for platforms that don't need them
    $relativeUrl = trim(lstrip(fetchContent("{$upstream}/{$relativeUrl}", 1)['url'], '/', 3), '/');
    $platform = PLATFORMS_REDIRECTS[$upstream];
    return "{$platform}/{$relativeUrl}";
}

function postRequest(string $url, string $body, array $headers=null): string|false {
    return file_get_contents($url, false, stream_context_create(['http' => [
        'header' => $headers,
        'method' => 'POST',
        'content' => $body,
    ]]));
}

function getCobaltVideo(string $url): string|null {
    $cobaltData = json_decode(postRequest(COBALT_API, json_encode(['url' => $url]), [
        'Accept: application/json',
        'Content-Type: application/json',
    ]));
    if ($cobaltData->status === 'redirect' && strpos($cobaltData->url, '.mp4')) {
        return $cobaltData->url;
    } else if ($cobaltData->status === 'tunnel' && strpos($cobaltData->filename, '.mp4')) {
        return SCRIPT_NAME . '__cobaltproxy__/_/' . lstrip($cobaltData->url, '/', 3);
    } else {
        return null;
    }
}

function fetchPageMedia(array &$item): void {
    $platform = $item['result']['platform'];
    $relativeUrl = $item['result']['relativeurl'];
    if ($api = platformMapGet($platform, PLATFORMS_API)) {
        $json = null;
        if ($apiUrl = $api['url'] ?? null) {
            $json = fetchContent($apiUrl . urlLast($relativeUrl))['body'];
        } else {
            $doc = htmldom(fetchContent(makeMediaScrapeUrl($item))['body']);
            if ($id = $api['id'] ?? null) {
                $json = $doc->getElementById($id)->textContent;
            } else if ($tag = $api['tag'] ?? null) {
                $item['result']['description'] = $doc->getElementsByTagName($tag)[0]->textContent;
                return;
            }
        }
        $data = json_decode($json, true);
        $values = [];
        foreach ($api['data'] as $key => $query) {
            $values[$key] = eval("return \$data{$query};");
        }
        $item['result'] = array_merge($item['result'], $values);
    } else {
        $cobaltVideo = null;
        if (COBALT_API && inPlatformArray($platform, PLATFORMS_COBALT)) {
            $cobaltVideo = getCobaltVideo($item['url']);
        }
        $html = fetchContent(makeMediaScrapeUrl($item))['body'];
        if (!$item['result']['video']) {
            $item['result']['video'] = $cobaltVideo ?? getAnyVideoUrl($html) ?? '';
        }
        if (!inPlatformArray($platform, PLATFORMS_NOIMAGES) /* !$immediateResult['image'] */) {
            $item['result']['images'] = getHtmlAttributes($html, 'img', 'src');
            // if (sizeof($immediateResult['images'])) {
            //     //$immediateResult['image'] = $imgs[0];
            // }
        }
    }
}

function getWebStreamUrls(string $absoluteUrl, string $options=''): array|null {
    if (($url = parseAbsoluteUrl($absoluteUrl)) && ($url = preg_replace('/[^A-Za-z0-9-_\/\.]/', '', $url))) {
        return explode("\n", trim(shell_exec("yt-dlp {$options} -g 'https://{$url}'")));
    } else {
        return null;
    }
}

function getYoutubeStreamUrl(string $relativeUrl): string {
    if ($video = preg_replace('/[^A-Za-z0-9-_]/', '', substr($relativeUrl, -11))) {
        return getWebStreamUrls("https://youtu.be/{$video}", '-f mp4')[0]; //trim(shell_exec("yt-dlp -g 'https://youtube.com/watch?v={$video}'"));
    }
}

function ffmpegStream(string $absoluteUrl): void {
    if ($urls = getWebStreamUrls($absoluteUrl, '--user-agent "' . USER_AGENT . '"')) {
        $inputs = '';
        foreach ($urls as $url) {
            $inputs .= " -i '{$url}' ";
        }
        header('Content-Type: video/mp4');
        passthru("ffmpeg -user_agent '" . USER_AGENT . "' {$inputs} -c:v copy -f ismv -");
    }
    die();
}

function streamFile(string $url, string $mime): void {
    header("Content-Type: {$mime}");
    readfile($url);
    die();
}

// TODO: redesign the endpoint names, they're kind of a mess
function handleApiRequest(array $segments): void {
	$api = substr($segments[0], 2, -2);
    $platform = $segments[1];
    $relativeUrl = implode('/', array_slice($segments, 2));
    if (($api === 'proxy' || $api === 'media')) {
        if ($platform === 'youtube') {
            header('Location: ' . getYoutubeStreamUrl($relativeUrl));
        } else if ($api === 'media' && end($segments) === '0') {
            $relativeUrl = substr($relativeUrl, 0, -2);
            $data = getPageData($platform, $relativeUrl)['result'];
            if ($url = ($data['video'] ?: $data['image'])) {
                header('Location: ' . $url);
            }
        }
    } else if ($api === 'fileproxy') {
        switch ($platform) {
            case 'youtube':
                streamFile(getYoutubeStreamUrl($relativeUrl), 'video/mp4');
                break;
            default:
                ffmpegStream('https://' . PLATFORMS[$platform][0] . '/' . lstrip($relativeUrl, '/', 3));
        }
    } else if ($api === 'cobaltproxy') {
        streamFile(COBALT_API . $relativeUrl, 'video/mp4');
    } else if ($api === 'embed') {
        header('Location: ' . makeEmbedUrl($platform, $relativeUrl));
    }
    die();
}

function linkifyUrls(string $text): string {
    return preg_replace(
        '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/',
        '<a href="$0" target="_blank" rel="noopener nofollow" title="$0">$0</a>',
        $text);
}

function iframeHtml(array $data): void {
    $platform = $data['result']['platform'];
    $relativeUrl = $data['result']['relativeurl'];
    if (inPlatformArray($platform, PLATFORMS_ORDERED)) { ?>
        <div>
            <a class="button"
                href="<?= abs(end(explode('/', $relativeUrl))-1) ?>">⬅️ Previous</a>
            <a class="button" style="float:right;"
                href="<?= end(explode('/', $relativeUrl))+1 ?>">➡️ Next</a>
        </div>
    <?php }
    ?>
    <iframe sandbox="allow-scripts allow-same-origin" allow="fullscreen" allowfullscreen="true"
        hidden="hidden" onload="this.hidden=false;"
        src="<?= htmlspecialchars(makeEmbedUrl($platform, $relativeUrl, $data['meta'])) ?>"></iframe>
    <?php
}

function historyItemHtml(array $item, bool $isSingle): void { ?> <div class="history-item <?php
    similar_text($item['title'], $item['description'], $percent);
    if ($percent > 90) echo 'ellipsize';
?>">
    <p class="title">
        <strong><?= htmlspecialchars($item['title']) ?></strong>
        <small><?= htmlspecialchars($item['platform']) ?><!-- <?= htmlspecialchars($item['datetime'] ?? '') ?> --></small>
    </p>
    <div style="text-align: center;">
        <?php if (($video = $item['htmlvideo'] ?: $item['video']) && $isSingle): ?>
            <div class="video">
                <video src="<?= htmlspecialchars($video) ?>" controls="controls"></video>
                <a class="button block" target="_blank" rel="noopener nofollow"
                    href="<?= htmlspecialchars($video) ?>"
                    download="<?= htmlspecialchars($item['title']); ?>">Download video</a>
            </div>
        <?php endif; ?>
        <?php if ($item['audio']): ?>
            <audio src="<?= htmlspecialchars($item['audio']) ?>" controls="controls"></audio>
        <?php endif; ?>
        <?php foreach (array_merge([$item['image']], $item['images']) as $image): ?>
            <a class="img" <?= $isSingle
                    ? 'href="' . htmlspecialchars($image ?? '') . '" target="_blank" rel="noopener nofollow"'
                    : 'href="' . htmlspecialchars(SCRIPT_NAME . $item['platform'] . '/' . $item['relativeurl']) . '"'
            ?>>
                <img src="<?= htmlspecialchars($image ?? '') ?>" onerror="this.hidden=true" />
            </a>
        <?php endforeach; ?>
    </div>
    <div>
        <p>
            <strong><?= htmlspecialchars($item['title']) ?></strong>
            <small><?= htmlspecialchars($item['platform']) ?><!-- <?= htmlspecialchars($item['datetime'] ?? '') ?> --></small>
        </p>
        <?php if ($item['description']): ?>
            <p class="description"><?= /*htmlspecialchars*/($item['description']) ?></p>
        <?php endif; ?>
        <?= actionsHtml($item) ?>
    </div>
</div> <?php }

function actionsHtml(array $item): void { ?> <p class="actions">
    <a class="button block external" target="_blank" rel="noopener nofollow" href="<?= htmlspecialchars(makeCanonicalUrl($item)) ?>">
        Original on <code><?= htmlspecialchars(PLATFORMS[$item['platform']][0] ?: $item['platform']) ?>/<?= htmlspecialchars($item['relativeurl']) ?></code>
    </a>
    <a class="button block internal" href="<?= htmlspecialchars(SCRIPT_NAME . $item['platform'] . '/' . $item['relativeurl']) ?>" <?php if (readProxatoreParam('viewmode') === 'embed') echo 'target="_blank"'; ?>>
        <?= readProxatoreParam('viewmode') === 'embed' ? ('Powered by ' . APP_NAME) : (APP_NAME . ' Permalink') ?>
    </a>
</p> <?php }

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

    // after refactoring this now treats aliases as canonical, we should decide on the matter
    if (str_starts_with($upstream, '__') && str_ends_with($upstream, '__')) {
        return handleApiRequest($segments);
    } else if (isExactPlatformName($upstream)) {
        $domain = PLATFORMS[$platform = $upstream][0];
    } else if ($platform = platformFromUpstream($upstream)) {
        return redirectTo($platform . '/' . $relativeUrl);
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

    if ($finalData = getPageData($platform, $relativeUrl)) {
        http_response_code($finalData['code']);
        fetchPageMedia($finalData);
        //if ($immediateResult['title'] || $immediateResult['description']) {
        //    saveHistory($immediateResult);
        //} else 
        if ($finalData['code'] >= 400) {
            $searchResults = searchExactHistory($platform, $finalData['result']['relativeurl']);
            if (sizeof($searchResults)) {
                $finalData['result'] = $searchResults[0];
            }
        } else if (readProxatoreBool('history')) {
            saveHistory($finalData['result']);
        }
        $finalData['result']['description'] = linkifyUrls($finalData['result']['description']);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= APP_NAME ?></title>
<meta name="description" content="<?= htmlspecialchars($finalData['result']['description'] ?? ucfirst(APP_DESCRIPTION)) ?>" />
<meta property="og:title" content="<?= htmlspecialchars($finalData['result']['title'] ?? APP_NAME) ?>" />
<meta property="og:description" content="<?= htmlspecialchars($finalData['result']['description'] ?? ucfirst(APP_DESCRIPTION)) ?>" />
<!-- <meta property="og:locale" content="<?= htmlspecialchars($finalData['result']['locale'] ?? '') ?>" /> -->
<meta property="og:type" content="<?= htmlspecialchars($finalData['result']['type'] ?? '') ?>" />
<?php if ($image = $finalData['result']['image'] ?? null): ?>
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:image" content="<?= htmlspecialchars($image) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($image) ?>" />
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
    <meta property="og:url" content="<?= htmlspecialchars(makeCanonicalUrl($result)) ?>" />
    <link rel="canonical" href="<?= htmlspecialchars(makeCanonicalUrl($result)) ?>" />
<?php else: ?>
    <meta property="og:url" content="<?= htmlspecialchars(makeSelfUrl()) ?>" />
    <link rel="canonical" href="<?= htmlspecialchars(makeSelfUrl()) ?>" />
<?php endif; ?>
<!-- <link rel="alternate" type="application/json+oembed" href="" />
<link rel="alternate" type="application/xml+oembed" href="" /> -->
<meta name="google-site-verification" content="<?= GOOGLE_VERIFICATION ?>" />
<meta name="msvalidate.01" content="<?= BING_VERIFICATION ?>" />
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
    height: 90vh;
    border: none;
}
.container {
    max-width: 1200px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
body.normal .container {
    width: 90%;
    margin: 20px;
}
body.embed .container {
    width: 100%;
}
.button {
    padding: 0.5em;
    border: 1px solid gray;
    border-radius: 8px;
    text-decoration: none;
    margin: 0.5em;
    display: inline-block;
}
.button.block {
    display: block;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    width: -moz-available;
    width: -webkit-fill-available;
}
.button.block code {
    text-decoration: underline;
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
}
body.normal .history-item {
    padding: 15px 0;
    border-bottom: 1px solid #e6e6e6;
    transition: background-color 0.3s;
}
body.normal .history-item:hover {
    background-color: #f9f9f9;
}
.history-item img, .history-item video, .history-item .video {
    width: 100%;
    max-width: 100%;
}
.history-item img, .history-item video {
    /*width: 49%;
    max-width: 49%;*/
    /* max-width: 100px;
    max-height: 100px; */
    /* margin-right: 15px; */
    border-radius: 4px;
    /* object-fit: cover; */
}
.history-item div {
    /*display: flex;*/
    flex-direction: column;
    justify-content: center;
    max-width: 49%;
    width: 49%;
    /*padding: 1em;*/
}
.img {
    display: inline-block;
}
img, .video {
    padding: 1em;
}
img[src=""], video[src=""] {
    display: none;
}
.img + .img,
.video:not(video[src=""]) + .img {
    max-width: 45% !important;
}
.description {
    white-space: preserve-breaks;
    border-left: 2px solid black;
    padding: 1em;
    word-break: break-word;
}
.history-item strong {
    font-size: 1.2rem;
    color: #1c1e21;
    margin-bottom: 5px;
    display: -webkit-box;
}
.history-item.ellipsize strong {
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.history-item small {
    font-size: 0.9rem;
    color: #606770;
}
.history-item .title {
    display: none;
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
ul.platforms a {
    text-decoration: none;
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
        width: 100%;
    }
    .history-item .title {
        display: block;
    }
}
/* @media (prefers-color-scheme: dark) {
    body {
        background-color: #444;
        color: white;
    }
    .container {
        background-color: #222;
    }
    .history-item strong {
        color: white;
    }
    .history-item:hover {
        background-color: #333;
    }
    a {
        color:rgb(85, 155, 247);
    }
} */
</style>
</head>
<body class="<?= readProxatoreParam('viewmode'); ?>">
<div class="container">
<?php if (readProxatoreParam('viewmode') !== 'embed'): ?>
    <h1><a href="<?= SCRIPT_NAME ?>"><?= APP_NAME; ?></a></h1>
    <?= $errorMessage ?>
    <form method="GET" action="<?= SCRIPT_NAME ?>">
        <div class="search-bar">
            <input type="text" required="required" name="proxatore-search" placeholder="Search or Input URL"
                value="<?= htmlspecialchars(readProxatoreParam('search') ?? makeCanonicalUrl($finalData['result'] ?? null) ?: ($group = readProxatoreParam('group') ? makeSelfUrl('?proxatore-group=' . urlencode($group)) : '')) ?>" />
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
        $platforms .= "</a></li><li><a href='{$searchPrefix}.{$platform}\",\"relativeurl\"'>{$platform}";
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
    </p>';
} ?>
<?php if (($finalData ?? null) && readProxatoreBool('embedfirst') && readProxatoreParam('viewmode') !== 'embed') iframeHtml($finalData); ?>
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
<?php if (($finalData ?? null) && !readProxatoreBool('embedfirst') && readProxatoreParam('viewmode') !== 'embed') iframeHtml($finalData); ?>
</div>
<script>(function(){
const groupLink = (group) => `?proxatore-group=${encodeURIComponent(JSON.stringify(group))}`;
const groupRedirect = (group) => location.href = groupLink(group);
const groupPersist = (group) => localStorage.setItem('proxatore-group', group.length ? JSON.stringify(group) : null);
const groupUpdate = (group) => {
    groupPersist(group);
    groupRedirect(group);
};
const moveItem = (data, from, to) => data.splice(to, 0, data.splice(from, 1)[0]);
const openingGroup = JSON.parse((new URLSearchParams(location.search)).get('proxatore-group'));
const editingGroup = JSON.parse(localStorage.getItem('proxatore-group'));
let group = openingGroup || editingGroup;
if (group) {
    document.querySelector('form').innerHTML += '<details id="ProxatoreGroup" style="margin-bottom: 20px;"><summary>Results Group</summary><ul></ul></details>';
    if (editingGroup) {
        ProxatoreGroup.open = true;
        ProxatoreGroup.querySelector('summary').innerHTML = `<a href="${groupLink(group)}">Results Group</a>`;
    }
    ProxatoreGroup.querySelector('summary').innerHTML += ` <button>${editingGroup ? 'Cancel' : 'Edit'}</button>`;
    ProxatoreGroup.querySelector('summary button').addEventListener('click', (ev) => {
        ev.preventDefault();
        groupUpdate(editingGroup ? [] : group);
    });
    ProxatoreGroup.querySelector('ul').innerHTML = Object.keys(group).map(id => `<li data-id="${id}">
        <button class="up">⬆</button> <button class="down">⬇</button> <button class="remove">Remove</button>
        <code><a href="<?= makeSelfUrl() ?>${group[id]}">${group[id]}</a></code>
    </li>`).join('');
    ProxatoreGroup.querySelectorAll('ul button.remove').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        group.splice(button.parentElement.dataset.id, 1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelectorAll('ul button.up').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        const id = button.parentElement.dataset.id;
        moveItem(group, id, id-1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelectorAll('ul button.down').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        const id = button.parentElement.dataset.id;
        moveItem(group, id, id+1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelector('ul li:first-of-type button.up').disabled = ProxatoreGroup.querySelector('ul li:last-of-type button.down').disabled = true;
} else {
    group = [];
}
document.querySelectorAll('.actions').forEach(item => {
    item.innerHTML += `<button class="button block">Add to Results Group</button>`;
    item.querySelector('button').addEventListener('click', () => {
        group.push(item.querySelector('a.internal').getAttribute('href'));
        groupUpdate(group);
    });
});
})();</script>
<!-- Page rendered in <?= (hrtime(true) - $startTime)/1e+6 ?> ms -->
</body>
</html>