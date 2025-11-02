<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

const PLATFORMS = [
    'spaccbbs' => ['bbs.spacc.eu.org'],
    'github' => ['github.com'],
    'github-gist' => ['gist.github.com'],
    'aliexpress' => ['aliexpress.com'],
    'bilibili' => ['bilibili.com'],
    'bluesky' => ['bsky.app'],
    'facebook' => ['facebook.com', 'm.facebook.com'],
    // 'giphy' => ['giphy.com'],
    'instagram' => ['instagram.com'],
    //'juxt' => ['juxt.pretendo.network'],
    'medium' => ['medium.com'],
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

const PLATFORMS_FAKESUBDOMAINS = ['aliexpress.com', 'pinterest.com'];

const PLATFORMS_USERSITES = ['altervista.org', 'blogspot.com', 'medium.com', 'wordpress.com'];

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
    'instagram' => ['ddinstagram.com', 'd.ddinstagram.com', 'kkinstagram.com', 'eeinstagram.com'],
    'reddit' => ['vxreddit.com'],
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

const PLATFORMS_USEPROXY = ['bluesky', 'reddit', 'twitter', 'x'];
const PLATFORMS_COBALT = ['instagram', 'threads', 'bilibili', 'pinterest'];

const PLATFORMS_FAKE404 = ['telegram'];

const PLATFORMS_ORDERED = ['telegram'];

// const PLATFORMS_VIDEO = ['youtube', 'bilibili']; // ['facebook', 'instagram'];

const PLATFORMS_WEBVIDEO = ['raiplay'];

const PLATFORMS_CSSIMAGES = ['telegram'];

const PLATFORMS_NOIMAGES = ['altervista.org', 'wordpress.com', 'medium'];

const PLATFORMS_NOEMBED = ['aliexpress', 'medium', 'pinterest'];

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
    'pinterest' => 'assets.pinterest.com/ext/embed.html?id=',
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
    'threads' => '/embed',
];

define('EMBEDS_PREFIXES_FULL', [
    'facebook' => 'www.facebook.com/plugins/post.php?href=' . urlencode('https://www.facebook.com/'),
]);
