<?php
/**
 * Handles enqueuing of scripts and styles for the LunaTV Player.
 *
 * @package LunaTV_Player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LunaTV_Player_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    /**
     * Register (but do NOT enqueue) assets.
     * The shortcode handler enqueues them only on pages that actually use the player,
     * avoiding bloat on pages that don't.
     */
    public function register_assets() {
        // Plyr (CSS + JS) — pinned versions to avoid surprises.
        wp_register_style(
            'lunatv-plyr',
            'https://cdn.plyr.io/3.7.8/plyr.css',
            array(),
            '3.7.8'
        );

        wp_register_script(
            'lunatv-plyr',
            'https://cdn.plyr.io/3.7.8/plyr.polyfilled.js',
            array(),
            '3.7.8',
            true
        );

        // hls.js.
        wp_register_script(
            'lunatv-hls',
            'https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js',
            array(),
            '1.5.13',
            true
        );

        // Plugin CSS (brand colors, watermark, layout).
        wp_register_style(
            'lunatv-player',
            LUNATV_PLAYER_URL . 'assets/css/lunatv-player.css',
            array( 'lunatv-plyr' ),
            LUNATV_PLAYER_VERSION
        );

        // Plugin JS (initializes hls.js + Plyr).
        wp_register_script(
            'lunatv-player',
            LUNATV_PLAYER_URL . 'assets/js/lunatv-player.js',
            array( 'lunatv-hls', 'lunatv-plyr' ),
            LUNATV_PLAYER_VERSION,
            true
        );
    }

    /**
     * Called by the shortcode to actually load the assets on demand.
     */
    public static function enqueue() {
        wp_enqueue_style( 'lunatv-plyr' );
        wp_enqueue_style( 'lunatv-player' );
        wp_enqueue_script( 'lunatv-hls' );
        wp_enqueue_script( 'lunatv-plyr' );
        wp_enqueue_script( 'lunatv-player' );
    }
}
