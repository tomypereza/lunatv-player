<?php
/**
 * Admin settings page under Settings → LunaTV Player.
 *
 * @package LunaTV_Player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LunaTV_Player_Settings {

    const OPTION_GROUP = 'lunatv_player_settings';
    const OPTION_NAME  = 'lunatv_player_stream_url';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_menu() {
        add_options_page(
            __( 'LunaTV Player', 'lunatv-player' ),
            __( 'LunaTV Player', 'lunatv-player' ),
            'manage_options',
            'lunatv-player',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => LUNATV_PLAYER_DEFAULT_STREAM,
            )
        );

        add_settings_section(
            'lunatv_player_main',
            __( 'Configuración del stream', 'lunatv-player' ),
            function () {
                echo '<p>' . esc_html__( 'URL del stream HLS (.m3u8) que reproducirá el shortcode [lunatv-player].', 'lunatv-player' ) . '</p>';
            },
            self::OPTION_GROUP
        );

        add_settings_field(
            self::OPTION_NAME,
            __( 'URL del stream (.m3u8)', 'lunatv-player' ),
            array( $this, 'render_field' ),
            self::OPTION_GROUP,
            'lunatv_player_main'
        );
    }

    public function render_field() {
        $value = get_option( self::OPTION_NAME, LUNATV_PLAYER_DEFAULT_STREAM );
        printf(
            '<input type="url" name="%s" value="%s" class="regular-text" placeholder="https://example.com/live/stream.m3u8" />',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $value )
        );
        echo '<p class="description">' .
            esc_html__( 'Por defecto, el plugin viene con el stream oficial de LunaTV. Solo cambia este valor si tienes un URL distinto.', 'lunatv-player' ) .
            '</p>';
    }

    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'LunaTV Player', 'lunatv-player' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::OPTION_GROUP );
                submit_button();
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Cómo usar el plugin', 'lunatv-player' ); ?></h2>
            <p><?php esc_html_e( 'Pega este shortcode en cualquier entrada, página o widget de texto:', 'lunatv-player' ); ?></p>
            <p><code>[lunatv-player]</code></p>

            <h3><?php esc_html_e( 'Atributos opcionales', 'lunatv-player' ); ?></h3>
            <ul style="list-style:disc; padding-left:20px;">
                <li><code>width="720"</code> — <?php esc_html_e( 'Ancho máximo en píxeles.', 'lunatv-player' ); ?></li>
                <li><code>autoplay="false"</code> — <?php esc_html_e( 'Desactiva el autoplay.', 'lunatv-player' ); ?></li>
                <li><code>muted="false"</code> — <?php esc_html_e( 'Inicia con audio (solo si autoplay también está desactivado).', 'lunatv-player' ); ?></li>
            </ul>

            <h3><?php esc_html_e( 'Ejemplo', 'lunatv-player' ); ?></h3>
            <p><code>[lunatv-player width="800" autoplay="true"]</code></p>
        </div>
        <?php
    }
}
