<?php
/**
 * Registers the [lunatv-player] shortcode.
 *
 * @package LunaTV_Player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LunaTV_Player_Shortcode {

    public function __construct() {
        add_shortcode( 'lunatv-player', array( $this, 'render' ) );
    }

    /**
     * Render the player.
     *
     * Supported attributes:
     *   width    - Max width in px (default: 100% of container).
     *   autoplay - "true" | "false" (default: true).
     *   muted    - "true" | "false" (default: true; required for autoplay).
     *
     * Example:
     *   [lunatv-player]
     *   [lunatv-player width="720"]
     *   [lunatv-player autoplay="false" muted="false"]
     */
    public function render( $atts ) {
        $atts = shortcode_atts(
            array(
                'width'    => '',
                'autoplay' => 'true',
                'muted'    => 'true',
            ),
            $atts,
            'lunatv-player'
        );

        // Resolve the stream URL: filter > option > default constant.
        $stream_url = get_option( 'lunatv_player_stream_url', LUNATV_PLAYER_DEFAULT_STREAM );
        /**
         * Filter the stream URL right before render.
         * Lets developers override the URL programmatically.
         */
        $stream_url = apply_filters( 'lunatv_player_stream_url', $stream_url );

        if ( empty( $stream_url ) || 'STREAM_URL_PLACEHOLDER' === $stream_url ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<div class="lunatv-player-error">' .
                    esc_html__( 'LunaTV Player: Configure el URL del stream en Ajustes → LunaTV Player.', 'lunatv-player' ) .
                    '</div>';
            }
            return ''; // Silent for visitors.
        }

        // Load the assets only when the shortcode is actually used.
        LunaTV_Player_Assets::enqueue();

        // Unique ID per player instance (allows multiple on one page if ever needed).
        $instance_id = 'lunatv-player-' . wp_unique_id();

        $autoplay = 'true' === strtolower( $atts['autoplay'] );
        $muted    = 'true' === strtolower( $atts['muted'] ) || $autoplay; // Autoplay requires muted.

        $logo_url = apply_filters(
            'lunatv_player_watermark_logo',
            LUNATV_PLAYER_URL . 'assets/images/lunatv-watermark.png'
        );

        $style = '';
        if ( ! empty( $atts['width'] ) && is_numeric( $atts['width'] ) ) {
            $style = 'max-width:' . absint( $atts['width'] ) . 'px;';
        }

        // Endpoint del contador (option > filtro). Vacío = contador oculto.
        $views_endpoint = get_option( 'lunatv_player_views_endpoint', LUNATV_PLAYER_DEFAULT_VIEWS_ENDPOINT );
        $views_endpoint = apply_filters( 'lunatv_player_views_endpoint', $views_endpoint );

        $config = array(
            'streamUrl'     => esc_url_raw( $stream_url ),
            'autoplay'      => $autoplay,
            'muted'         => $muted,
            'viewsEndpoint' => $views_endpoint ? esc_url_raw( $views_endpoint ) : '',
        );

        ob_start();
        ?>
        <div class="lunatv-player-wrap" style="<?php echo esc_attr( $style ); ?>">
            <div class="lunatv-player-aspect">
                <video
                    id="<?php echo esc_attr( $instance_id ); ?>"
                    class="lunatv-player-video"
                    controls
                    playsinline
                    <?php echo $autoplay ? 'autoplay' : ''; ?>
                    <?php echo $muted ? 'muted' : ''; ?>
                ></video>
                <img
                    src="<?php echo esc_url( $logo_url ); ?>"
                    alt="LunaTV"
                    class="lunatv-player-watermark"
                    aria-hidden="true"
                />
                <?php if ( ! empty( $config['viewsEndpoint'] ) ) : ?>
                <div class="lunatv-player-views" hidden>
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M2.5 12C3.7 7.9 7.5 5 12 5s8.3 2.9 9.5 7c-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <span class="lunatv-player-count">—</span>
                </div>
                <?php endif; ?>
                <a
                    href="https://lunatv.do"
                    class="lunatv-player-attribution"
                    target="_blank"
                    rel="noopener"
                ><?php echo esc_html__( 'En vivo · LunaTV.do', 'lunatv-player' ); ?></a>
            </div>
            <script type="application/json" class="lunatv-player-config" data-target="<?php echo esc_attr( $instance_id ); ?>">
                <?php echo wp_json_encode( $config ); ?>
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}
