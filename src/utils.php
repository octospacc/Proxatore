<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

require 'history.php';

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

function platformFromDomain(string $upstream): string|null {
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
        : platformFromAlias($upstream) ?? platformFromDomain($upstream));
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
    return getRequestProtocol() . '://' . $_SERVER['HTTP_HOST'] . SCRIPT_NAME . $str;
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !ALLOW_NONSECURE_SSL);
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

function makeInternalBareUrl(string $platform, string $relativeUrl): string {
    return "{$platform}/{$relativeUrl}";
}

function makeInternalItemUrl(array $item): string {
    if ($result = $item['result']) {
        $item = $result;
    }
    return makeInternalBareUrl($item['platform'], $item['relativeurl']);
}

function makeCanonicalBareUrl(string $platform, string $relativeUrl): string {
    return 'https://' . (PLATFORMS[$platform][0] ?: $platform) . '/' . $relativeUrl;
}

function makeCanonicalItemUrl(array|null $item): string|null {
    return ($item
        ? makeCanonicalBareUrl($item['platform'], $item['relativeurl'])
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
    //     return $api['prefix'] . makeCanonicalItemUrl(['platform' => $platform, 'relativeurl' => $relativeUrl]) . $api['suffix'];
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
        'image' => $meta['og:image'] ?? $meta['twitter:image'] ?? '',
        'video' => $meta['og:video'] ?? $meta['og:video:url'] ?? '',
        'videotype' => $meta['og:video:type'] ?? '',
        'htmlvideo' => $meta['og:video'] ?? $meta['og:video:url'] ?? '',
        'audio' => $meta['og:audio'] ?? '',
        'title' => $meta['og:title'] ?? $meta['og:title'] ?? $meta['twitter:title'] ?? '',
        //'author' => $meta['twitter:creator'] ?? $meta['og:site_name'] ?? '',
        'description' => $meta['og:description'] ?? $meta['description'] ?? '',
        'images' => [],
    ];
    if (inPlatformArray($platform, PLATFORMS_WEBVIDEO) && !$data['video']) {
        $data['video'] = makeCanonicalItemUrl($data);
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
    // TODO: strip query params for platforms that don't need them
    return makeInternalBareUrl(
        PLATFORMS_REDIRECTS[$upstream],
        trim(lstrip(fetchContent(makeInternalBareUrl($upstream, $relativeUrl), 1)['url'], '/', 3), '/'));
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
                $item['result']['description'] = $doc->getElementsByTagName($tag)[0]->textContent ?? '';
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
        return getWebStreamUrls("https://youtu.be/{$video}", '-f mp4')[0];
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
                ffmpegStream(makeCanonicalBareUrl($platform, lstrip($relativeUrl, '/', 3)));
        }
    } else if ($api === 'cobaltproxy') {
        streamFile(COBALT_API . $relativeUrl, 'video/mp4');
    } else if ($api === 'embed') {
        header('Location: ' . makeEmbedUrl($platform, $relativeUrl));
    } else if ($api === 'randominstance') {
        // header('Location: ' . randomProxatoreInstance() . '/' . implode('/', array_slice($segments, 1)));
        header('Location: ' . randomProxatoreInstance() . ($platform && $relativeUrl ? ('?proxatore-search=' . urlencode(makeCanonicalBareUrl($platform, $relativeUrl))) : ''));
    }
    die();
}

function linkifyUrls(string $text): string {
    return preg_replace(
        '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/',
        '<a href="$0" target="_blank" rel="noopener nofollow" title="$0">$0</a>',
        $text);
}

function randomProxatoreInstance() {
    preg_match_all('/\|<([^>]+)>/', file_get_contents('https://gitlab.com/octospacc/Proxatore/-/raw/main/README.md'), $matches);
    $urls = $matches[1];
    return rtrim($urls[array_rand($urls)], '/');
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data, bool $strict=false): string {
    return base64_decode(strtr($data, '-_', '+/'), $strict);
}

function backgroundExec(string $command): void {
    if (explode(' ', php_uname('s'))[0] === "Windows") {
        pclose(popen("start /B {$command} &", 'r'));
    } else {
        shell_exec("{$command} &");
    }
}

function hash_base64(string $algo, string $data): string {
    return base64url_encode(hash($algo, $data, true));
}
