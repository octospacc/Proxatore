# 🎭 Proxatore

Proxatore is a content proxy for viewing and embedding media and text from various platforms.

**Try it now!** Various instances of Proxatore are available (feel free to add your own with a pull request!):

|URL|Country|Uses Cloudflare|Notes|
|-|-|-|-|
|**<https://proxatore.octt.eu.org/>**|**🇮🇹**||**Official instance**|
|<https://proxatore.almi.eu.org/>|🇮🇹|||
|<https://proxatore.ct.ws/>|🇬🇧||Only works in browser|
|<https://octospacc.altervista.org/proxatore/>|🇩🇪|||
|<https://laprovadialessioalmi.altervista.org/proxatore/>|🇩🇪|⚠️||
|<https://proxatore.lucy.gq>|🇫🇷|||

Source code mirrors:

* GitLab (primary): <https://gitlab.com/octospacc/Proxatore>
* GitHub: <https://github.com/octospacc/Proxatore>
* Gitea.it: <https://gitea.it/octospacc/Proxatore>

## How to self-host?

This software is free and open-source, and you can host it on your own server, for either private or public use. Ensure you meet the following requirements, then download the source code and review the configuration file.

### Base requirements

<dl>
    <dt>A web server with PHP</dt>
        <dd>(Currently only tested on nginx with PHP 8.2 and IIS with PHP 8.3, as of May 2025.)</dd>
    <dt><code>curl</code>, <code>dom</code>, <code>mbstring</code>, <code>openssl</code> and <code>xml</code> PHP extensions</dt>
        <dd>The program requires these PHP extensions to be installed and enabled on the server to work.</dd>
</dl>

### Optional requirements

<dl>
    <dt>A dedicated domain name</dt>
        <dd>To host the program properly, instead of in a subpath.</dd>
    <dt><a href="https://github.com/yt-dlp/yt-dlp" target="_blank">yt-dlp</a> on your server</dt>
        <dd>To stream videos from various platforms in MP4 format.</dd>
    <dt>A <a href="https://github.com/imputnet/cobalt">cobalt</a> API server</dt>
        <dd>To have a fallback for access to media files for the most popular platforms.</dd>
</dl>
