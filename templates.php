<?php
/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

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
                    : 'href="' . htmlspecialchars(SCRIPT_NAME . makeInternalItemUrl($item)) . '"'
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
            <p class="description"><?= linkifyUrls(htmlspecialchars($item['description'])) ?></p>
        <?php endif; ?>
        <?= actionsHtml($item) ?>
    </div>
</div> <?php }

function actionsHtml(array $item): void { ?> <p class="actions">
    <a class="button block external" target="_blank" rel="noopener nofollow" href="<?= htmlspecialchars(makeCanonicalItemUrl($item)) ?>">
        Original on <code><?= htmlspecialchars(PLATFORMS[$item['platform']][0] ?: $item['platform']) ?>/<?= htmlspecialchars($item['relativeurl']) ?></code>
    </a>
    <a class="button block internal" href="<?= htmlspecialchars(SCRIPT_NAME . makeInternalItemUrl($item)) ?>" <?php if (readProxatoreParam('viewmode') === 'embed') echo 'target="_blank"'; ?>>
        <?= readProxatoreParam('viewmode') === 'embed' ? ('Powered by ' . APP_NAME) : (APP_NAME . ' Permalink') ?>
    </a>
</p> <?php }
