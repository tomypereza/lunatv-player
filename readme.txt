=== LunaTV Player ===
Contributors: lunatvrd
Tags: hls, live streaming, video player, plyr, hls.js, lunatv
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

Embed the LunaTV Canal 25 live stream on any WordPress site using a simple shortcode.

== Description ==

LunaTV Player is a lightweight WordPress plugin that lets you embed the LunaTV Canal 25 live stream on any post, page, or widget area using the shortcode `[lunatv-player]`.

It uses **hls.js** for HLS playback in browsers without native support and **Plyr** as the user interface, providing a polished player experience that works on desktop, mobile, and tablets.

= Features =

* Simple `[lunatv-player]` shortcode — no configuration needed
* HLS streaming via hls.js with adaptive quality
* Native HLS playback on Safari / iOS
* Branded with LunaTV colors and a discreet corner watermark
* Live indicator with pulsing red dot
* Quality selector (auto + manual)
* PiP, AirPlay, fullscreen support
* Responsive 16:9 layout
* Spanish UI by default
* Assets loaded only on pages that use the shortcode (no bloat)

= Usage =

Just paste this in any post or page:

`[lunatv-player]`

Optional attributes:

* `width="720"` — Limit the maximum width (px)
* `autoplay="false"` — Disable autoplay
* `muted="false"` — Start with audio (only if `autoplay="false"`)

Example:

`[lunatv-player width="800" autoplay="true"]`

= Configuration =

Go to **Settings → LunaTV Player** in your WordPress admin to change the default stream URL if needed.

== Installation ==

1. Upload the `lunatv-player` folder to `/wp-content/plugins/`, or install the ZIP from the Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. (Optional) Visit **Settings → LunaTV Player** to verify the stream URL.
4. Add `[lunatv-player]` to any post, page, or text widget.

== Frequently Asked Questions ==

= Why doesn't autoplay work? =

Most browsers only allow autoplay when the player is muted. The plugin starts muted by default to ensure autoplay works reliably.

= Can I use this for streams other than LunaTV? =

Yes — change the stream URL in **Settings → LunaTV Player**. The player itself is generic and supports any HLS `.m3u8` source.

= Does it work on mobile? =

Yes. The plugin uses native HLS on iOS/Safari and hls.js on all other browsers, with a responsive layout.

== Changelog ==

= 1.0.0 =
* Initial release.
