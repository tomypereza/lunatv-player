/**
 * LunaTV Player — Frontend bootstrap
 *
 * Finds every <script class="lunatv-player-config"> on the page, reads its JSON
 * configuration, and wires up hls.js + Plyr for the associated <video> element.
 *
 * Works on every modern browser:
 *   - Native HLS (Safari, iOS) → uses the <video> element directly.
 *   - Other browsers → uses hls.js as the source extender.
 */
( function () {
    'use strict';

    function initPlayer( video, config ) {
        if ( ! video || ! config || ! config.streamUrl ) {
            return;
        }

        var streamUrl = config.streamUrl;
        var isNativeHls = video.canPlayType( 'application/vnd.apple.mpegurl' );
        var hlsInstance = null;

        // Plyr controls — minimal, live-stream friendly.
        var plyrOptions = {
            controls: [
                'play-large',
                'play',
                'mute',
                'volume',
                'settings',
                'pip',
                'airplay',
                'fullscreen'
            ],
            settings: [ 'quality' ],
            autoplay: !! config.autoplay,
            muted: !! config.muted,
            clickToPlay: true,
            hideControls: true,
            ratio: '16:9',
            i18n: {
                play: 'Reproducir',
                pause: 'Pausar',
                mute: 'Silenciar',
                unmute: 'Activar audio',
                enterFullscreen: 'Pantalla completa',
                exitFullscreen: 'Salir de pantalla completa',
                settings: 'Ajustes',
                quality: 'Calidad',
                qualityLabel: { 0: 'Auto' }
            }
        };

        function attachQualityLevels( hls, player ) {
            // Expose hls.js quality levels via Plyr's settings menu.
            hls.on( window.Hls.Events.MANIFEST_PARSED, function () {
                var levels = hls.levels.map( function ( lvl ) {
                    return lvl.height;
                } );
                // Include 0 (auto).
                var qualities = [ 0 ].concat( levels );

                player.options.quality = {
                    default: 0,
                    options: qualities,
                    forced: true,
                    onChange: function ( newQuality ) {
                        if ( newQuality === 0 ) {
                            hls.currentLevel = -1; // Auto.
                            return;
                        }
                        hls.levels.forEach( function ( level, idx ) {
                            if ( level.height === newQuality ) {
                                hls.currentLevel = idx;
                            }
                        } );
                    }
                };
            } );
        }

        function autoplayAfterReady( player ) {
            if ( ! config.autoplay ) {
                return;
            }
            // Most browsers require muted+playsinline for autoplay.
            player.muted = true;
            var p = player.play();
            if ( p && typeof p.catch === 'function' ) {
                p.catch( function () {
                    // Autoplay blocked. The user will press play manually.
                } );
            }
        }

        if ( isNativeHls ) {
            // Safari / iOS path — assign src directly, Plyr handles the rest.
            video.src = streamUrl;
            var nativePlayer = new window.Plyr( video, plyrOptions );
            nativePlayer.on( 'ready', function () {
                autoplayAfterReady( nativePlayer );
            } );
        } else if ( window.Hls && window.Hls.isSupported() ) {
            hlsInstance = new window.Hls( {
                lowLatencyMode: true,
                backBufferLength: 30
            } );
            hlsInstance.loadSource( streamUrl );
            hlsInstance.attachMedia( video );

            var hlsPlayer = new window.Plyr( video, plyrOptions );
            attachQualityLevels( hlsInstance, hlsPlayer );

            hlsInstance.on( window.Hls.Events.MANIFEST_PARSED, function () {
                autoplayAfterReady( hlsPlayer );
            } );

            // Recovery from network/media errors.
            hlsInstance.on( window.Hls.Events.ERROR, function ( _evt, data ) {
                if ( ! data.fatal ) {
                    return;
                }
                switch ( data.type ) {
                    case window.Hls.ErrorTypes.NETWORK_ERROR:
                        hlsInstance.startLoad();
                        break;
                    case window.Hls.ErrorTypes.MEDIA_ERROR:
                        hlsInstance.recoverMediaError();
                        break;
                    default:
                        hlsInstance.destroy();
                }
            } );
        } else {
            // No HLS support at all (very old browsers).
            video.outerHTML =
                '<div style="padding:24px;color:#fff;background:#0a0a0a;text-align:center;">' +
                'Tu navegador no soporta la transmisión en vivo. ' +
                '<a href="https://lunatv.do" style="color:#F5A623;">Ver en LunaTV.do</a></div>';
        }
    }

    function bootstrap() {
        var configs = document.querySelectorAll( '.lunatv-player-config' );
        configs.forEach( function ( node ) {
            var targetId = node.getAttribute( 'data-target' );
            var video = document.getElementById( targetId );
            if ( ! video || video.dataset.lunatvInitialized === '1' ) {
                return;
            }
            try {
                var cfg = JSON.parse( node.textContent );
                initPlayer( video, cfg );
                video.dataset.lunatvInitialized = '1';
            } catch ( e ) {
                /* Invalid config JSON — skip silently. */
            }
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', bootstrap );
    } else {
        bootstrap();
    }
} )();
