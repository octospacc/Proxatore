<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

if (php_sapi_name() !== 'cli') return;

require 'config.php';
require 'utils.php';

$platform = $argv[1];
$relativeUrl = $argv[2];

$index = HISTORY_FOLDER . "{$platform}/{$relativeUrl}.json";
$item = json_decode(explode("\n", file_get_contents($index))[0], true);
if ($item['image']) {
    [$folder, $file] = mediaUrlToPath($item['image']);
    $target = "{$folder}/{$file}";
    if (!file_exists($target)) {
        mkdir($folder, 0777, true);
        copy($item['image'], $target);
        // mkdir(CACHE_FOLDER, 0777, true);
        // $bytes = file_get_contents($item['image']);
        // $hash = hash_base64('sha256', $bytes);
        // $ext = extFromUrl($item['image']);
        // file_put_contents(CACHE_FOLDER . "{$hash}.{$ext}", $bytes);
        // file_put_contents($index, json_encode([
        //     'image' => null,
        // ]), FILE_APPEND);
    }
}
// TODO rename image to hash of content and add extra metadata to identify it in json file, to prevent duplication?

function mediaUrlToPath(string $url): array {
    $segments = explode('/', parseAbsoluteUrl($url));
    $folder = CACHE_FOLDER . '/' . $segments[0];
    $file = implode('/', array_slice($segments, 1));
    $hash = hash_base64('sha1', $file);
    $ext = extFromUrl($file);
    return [$folder, /* urlencode($file) */ substr(urlencode($file), 0, 32) . "-{$hash}.{$ext}"];
}

function extFromUrl(string $url): string {
    return end(explode('.', explode('?', $url)[0]));
}
