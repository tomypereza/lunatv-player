<?php
/**
 * Plugin Name:       LunaTV Player
 * Plugin URI:        https://lunatv.do
 * Description:       Embed the LunaTV Canal 25 live stream on any WordPress site using the shortcode [lunatv-player]. HLS-based player with hls.js + Plyr.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            LunaTV Canal 25
 * Author URI:        https://lunatv.do
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lunatv-player
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access.
}

// Plugin constants.
define( 'LUNATV_PLAYER_VERSION', '1.1.0' );
define( 'LUNATV_PLAYER_FILE', __FILE__ );
define( 'LUNATV_PLAYER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LUNATV_PLAYER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Default live stream URL.
 * Change this in wp-admin → Settings → LunaTV Player, or via filter:
 *   add_filter( 'lunatv_player_stream_url', fn() => 'https://your-cdn/stream.m3u8' );
 */
define( 'LUNATV_PLAYER_DEFAULT_STREAM', 'https://stream.lunatv.do/hls/prueba.m3u8' );

/**
 * Endpoint del contador global de reproducciones.
 * El player hace un POST al iniciar el play (lo cuenta una vez por sesión) y
 * un GET cada pocos segundos para mostrar el número. Configurable en
 * Ajustes → LunaTV Player o vía filtro 'lunatv_player_views_endpoint'.
 * Dejar vacío desactiva el contador.
 */
define( 'LUNATV_PLAYER_DEFAULT_VIEWS_ENDPOINT', 'https://lunatv.do/api/live/views' );

// Load plugin classes.
require_once LUNATV_PLAYER_PATH . 'includes/class-lunatv-player-assets.php';
require_once LUNATV_PLAYER_PATH . 'includes/class-lunatv-player-shortcode.php';
require_once LUNATV_PLAYER_PATH . 'includes/class-lunatv-player-settings.php';

/**
 * Bootstrap the plugin.
 */
function lunatv_player_init() {
    load_plugin_textdomain( 'lunatv-player', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    new LunaTV_Player_Assets();
    new LunaTV_Player_Shortcode();

    if ( is_admin() ) {
        new LunaTV_Player_Settings();
    }
}
add_action( 'plugins_loaded', 'lunatv_player_init' );

/**
 * Activation hook: set sensible defaults.
 */
function lunatv_player_activate() {
    if ( false === get_option( 'lunatv_player_stream_url' ) ) {
        add_option( 'lunatv_player_stream_url', LUNATV_PLAYER_DEFAULT_STREAM );
    }
    if ( false === get_option( 'lunatv_player_views_endpoint' ) ) {
        add_option( 'lunatv_player_views_endpoint', LUNATV_PLAYER_DEFAULT_VIEWS_ENDPOINT );
    }
}
register_activation_hook( __FILE__, 'lunatv_player_activate' );
