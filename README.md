# LunaTV Player — Plugin de WordPress

Plugin oficial de **[LunaTV Canal 25](https://lunatv.do)** para embeber la transmisión en vivo en cualquier sitio WordPress mediante un shortcode simple.

Construido sobre **hls.js** + **Plyr**, con la misma experiencia que el reproductor del sitio oficial.

---

## Características

- 🎬 Shortcode `[lunatv-player]` — sin configuración previa
- 📡 Streaming HLS con calidad adaptativa (hls.js + nativo en Safari/iOS)
- 🎨 Branding LunaTV: naranja/dorado, watermark de esquina, indicador "en vivo"
- ⚡ Carga de assets sólo en páginas que usan el shortcode
- 🌐 Idioma español por defecto, traducible
- 📱 Responsive 16:9 (mobile, tablet, desktop)
- 🎛️ Selector de calidad, PiP, AirPlay, pantalla completa
- 🔌 Compatible con cualquier tema de WordPress (CSS aislado con prefijo)

## Instalación

### Opción A — desde el ZIP de releases

1. Descargar el `.zip` de [Releases](../../releases).
2. WordPress → Plugins → **Añadir nuevo** → **Subir plugin** → seleccionar el `.zip`.
3. Activar.

### Opción B — manual

```bash
cd wp-content/plugins
git clone https://github.com/<usuario>/lunatv-player.git
```

Luego activar desde el panel de plugins.

## Uso

En cualquier entrada, página o widget de texto:

```
[lunatv-player]
```

### Atributos opcionales

| Atributo   | Valores         | Defecto | Descripción                                    |
|------------|-----------------|---------|------------------------------------------------|
| `width`    | número (px)     | 100%    | Ancho máximo del player                        |
| `autoplay` | `true` / `false`| `true`  | Reproducción automática (requiere `muted`)     |
| `muted`    | `true` / `false`| `true`  | Inicia silenciado                              |

### Ejemplos

```
[lunatv-player]
[lunatv-player width="720"]
[lunatv-player autoplay="false" muted="false"]
```

## Configuración

**Ajustes → LunaTV Player** en el panel de WordPress permite cambiar el URL del stream `.m3u8` si fuera necesario.

## Hooks para desarrolladores

### Filtro: URL del stream
```php
add_filter( 'lunatv_player_stream_url', function( $url ) {
    return 'https://otro-cdn/stream.m3u8';
} );
```

### Filtro: URL del logo del watermark
```php
add_filter( 'lunatv_player_watermark_logo', function( $url ) {
    return 'https://misitio.com/mi-logo.png';
} );
```

## Estructura del proyecto

```
lunatv-player/
├── lunatv-player.php              ← Bootstrap del plugin
├── readme.txt                     ← Formato WordPress.org
├── README.md                      ← Este archivo
├── LICENSE                        ← GPL-2.0
├── includes/
│   ├── class-lunatv-player-assets.php
│   ├── class-lunatv-player-shortcode.php
│   └── class-lunatv-player-settings.php
├── assets/
│   ├── js/lunatv-player.js
│   ├── css/lunatv-player.css
│   └── images/lunatv-watermark.png
└── languages/
```

## Stack técnico

- **PHP 7.4+** (requerido por WordPress)
- **JavaScript vanilla** (sin build step)
- **hls.js 1.5.13**
- **Plyr 3.7.8**
- **CSS plano con variables**, scopeado con prefijo `lunatv-player-*`

## Generar release

```bash
# Desde la raíz del repo
zip -r lunatv-player.zip lunatv-player \
  -x "*.git*" "*.md" "node_modules/*" ".github/*"
```

## Licencia

GPL-2.0 — ver [LICENSE](./LICENSE).

El stream y la marca **LunaTV** son propiedad de Luna TV Canal 25.
