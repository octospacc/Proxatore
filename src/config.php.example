<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

const APP_NAME = '🎭️ Proxatore';
const APP_DESCRIPTION = 'a content proxy for viewing and embedding media and text from various platforms.';

// if you make changes to the source code, please fill this to point to your modified version
const MODIFIED_SOURCE_CODE = '';

// cobalt API server URL; set to false or null or '' to avoid using cobalt
const COBALT_API = 'http://192.168.1.125:9010/';

const ALLOW_NONSECURE_SSL = false;

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

const SAVE_HISTORY = true;
const SEARCH_LIMIT = 30;

const GOOGLE_VERIFICATION = 'HjNf-db8xb7lkRNgD3Q8-qeF1lWsbxmCZptRyjLBnrI';
const BING_VERIFICATION = '45DC0FC265FF4059D48677970BE86150';

define('USER_AGENT', "Proxatore/2025/1 ({$_SERVER['HTTP_HOST']})");

define('SCRIPT_NAME', ($_SERVER['SCRIPT_NAME'] === '/' ? '/' : "{$_SERVER['SCRIPT_NAME']}/"));

const HISTORY_FILE = './Proxatore.history.jsonl';
const HISTORY_FOLDER = './history.d/';
const CACHE_FOLDER = './cache.d/';

// const OPTIONS_OVERRIDES = [
//     'bbs.spacc.eu.org' => [
//         'embedfirst' => true,
//     ],
// ];
