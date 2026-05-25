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

    /**
     * Contador global de reproducciones.
     * - POST una sola vez al iniciar el play (lo cuenta el server, dedup por sesión).
     * - GET cada 12s para mostrar el número en el badge.
     */
    function setupViewsCounter( video, config ) {
        if ( ! config.viewsEndpoint ) {
            return;
        }
        var wrap = video.closest( '.lunatv-player-wrap' );
        if ( ! wrap ) {
            return;
        }
        var badge = wrap.querySelector( '.lunatv-player-views' );
        var countEl = wrap.querySelector( '.lunatv-player-count' );
        if ( ! badge || ! countEl ) {
            return;
        }

        var endpoint = config.viewsEndpoint;
        var reported = false;

        function getSessionId() {
            var fallback = 'sid-' + Date.now() + '-' + Math.random().toString( 36 ).slice( 2 );
            try {
                var sid = localStorage.getItem( 'lunatv_sid' );
                if ( ! sid ) {
                    sid = ( window.crypto && typeof crypto.randomUUID === 'function' )
                        ? crypto.randomUUID()
                        : fallback;
                    localStorage.setItem( 'lunatv_sid', sid );
                }
                return sid;
            } catch ( e ) {
                return fallback;
            }
        }

        function formatNumber( n ) {
            try {
                return n.toLocaleString( 'es-DO' );
            } catch ( e ) {
                return String( n );
            }
        }

        // POST una sola vez cuando arranca la reproducción.
        video.addEventListener( 'playing', function () {
            if ( reported ) {
                return;
            }
            reported = true;
            try {
                fetch( endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify( { sessionId: getSessionId() } ),
                    keepalive: true
                } ).catch( function () {} );
            } catch ( e ) {
                /* noop */
            }
        } );

        // GET periódico para mostrar el número (no cuenta nada).
        function refresh() {
            fetch( endpoint )
                .then( function ( r ) {
                    return r.ok ? r.json() : null;
                } )
                .then( function ( d ) {
                    if ( d && typeof d.views === 'number' ) {
                        countEl.textContent = formatNumber( d.views );
                        badge.hidden = false;
                    }
                } )
                .catch( function () {} );
        }
        refresh();
        setInterval( refresh, 12000 );
    }

    function initPlayer( video, config ) {
        if ( ! video || ! config || ! config.streamUrl ) {
            return;
        }

        setupViewsCounter( video, config );

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
