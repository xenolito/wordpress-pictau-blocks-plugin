# Pictau Blocks Gutenberg

Plugin WordPress personalizado para crear bloques de contenido reutilizables e insertarlos en cualquier parte del sitio mediante shortcode.

- **Versión:** 4.0.2
- **Autor:** Oscar Rey Tajes ([@xenolito](mailto:oscar.rey.tajes@gmail.com))
- **Licencia:** GPL2
- **Carpeta del plugin:** `pictau-blocks-gutenberg` (no renombrar)

---

## Concepto

Un **Pictau Block** es una entrada del CPT `pictau_blocks`, editable con el editor Gutenberg, que no tiene URL pública propia. Su único propósito es almacenar contenido HTML/bloques para ser invocado desde cualquier otro lugar del sitio mediante el shortcode `[pictau-blocks id="XX"]`.

Casos de uso habituales:
- Submenús del menú de navegación (megamenús)
- Contenidos del footer
- Banners o bloques promocionales en sidebars (widget)
- Cualquier fragmento HTML reutilizable en múltiples páginas

---

## Custom Post Type: `pictau_blocks`

| Parámetro | Valor |
|---|---|
| Slug | `pictau_blocks` |
| Soporte del editor | `title`, `editor` (Gutenberg), `revisions`, `page-attributes` |
| Acceso público | No (`publicly_queryable: false`, `has_archive: false`) |
| Aparece en búsquedas | No (`exclude_from_search: true`) |
| Menú admin | Sí, posición 5, icono `dashicons-tagcloud` |
| Icono en admin | Nube de etiquetas |

Los bloques son jerárquicos (soportan parent/child) y aparecen en el menú de navegación del admin para poder asignarlos como ítems de menú si se necesita.

---

## Shortcode `[pictau-blocks id="XX"]`

Renderiza el contenido completo de un Pictau Block por su ID de post.

```
[pictau-blocks id="1234"]
```

### Parámetros

| Parámetro | Requerido | Descripción |
|---|---|---|
| `id` | Sí | ID del post `pictau_blocks` a renderizar |

### Proceso de renderizado

1. Comprueba si existe un transient cacheado para ese ID.
2. Si no hay caché: obtiene el post, ejecuta `do_blocks()` (pipeline completo de Gutenberg), aplica `shortcode_unautop()` para eliminar los `<p>` que `wpautop` inyecta alrededor de los shortcodes dentro de bloques Shortcode de Gutenberg, aplica `apply_shortcodes()`, elimina comentarios HTML de bloque, elimina etiquetas `<p>` vacías y aplica la sustitución de SVGs inline (`wp_svg_inline_filter`).
3. Guarda el resultado en un transient WordPress con TTL de **12 horas**.
4. Devuelve el HTML procesado.

> **Nota técnica:** El bloque nativo `core/shortcode` de Gutenberg almacena el shortcode en su innerHTML sin envolver en `<p>`. `do_blocks()` aplica `wpautop` internamente a ese innerHTML, generando `<p>[shortcode]</p>`. `shortcode_unautop()` elimina esos wrappers antes de que `apply_shortcodes()` expanda los shortcodes, evitando que el output quede envuelto en párrafos no deseados.

### Caché

- **Clave del transient:** `pictau_block_{id}`
- **TTL:** 12 horas
- **Invalidación automática:** al guardar o actualizar el post `pictau_blocks` correspondiente (hook `save_post_pictau_blocks`)

El caché hace que `do_blocks()` —que es costoso— se ejecute solo cuando el contenido cambia o expira el transient, no en cada petición.

### SVGs inline

El shortcode aplica automáticamente la función `wp_svg_inline_filter()` del tema al output del bloque. Esto sustituye cualquier `<img src="*.svg">` por el código SVG inline, permitiendo aplicar estilos CSS sobre los SVGs del bloque. La función `get_svg()` del tema usa caché estático en memoria para evitar lecturas de disco repetidas dentro del mismo request.

---

## Columna de shortcode en el listado admin

En la vista de listado de `pictau_blocks` en el admin de WordPress, la columna **Shortcode** muestra directamente el shortcode listo para copiar y pegar:

```
[pictau-blocks id="1234"]
```

---

## Widget: Pictau Blocks

Widget legacy para áreas de widgets clásicos (sidebars). Permite seleccionar un `pictau_blocks` de un desplegable y renderizar su contenido en el área de widget.

> **Nota:** Este widget renderiza el contenido con `do_shortcode()` directamente, sin pasar por el caché de transients ni por la sustitución de SVGs inline. Para contenido en sidebars con SVGs o rendimiento crítico, es preferible usar el shortcode directamente.

---

## Página de ajustes

Accesible desde **Ajustes > PICTAU-BLOCKS Settings** en el admin de WordPress. Actualmente sirve como página informativa del plugin; los campos de configuración están deshabilitados.

---

## Archivos del plugin

```
pictau-blocks-gutenberg/
├── pictau-blocks-gutenberg.php   # Archivo principal: CPT, widget, settings page
├── pictau-blocks-shortcodes.php  # Shortcode [pictau-blocks id="XX"] con caché
├── css/
│   ├── afmp-style.css            # Estilos legacy (no cargados)
│   └── reset.css                 # Reset legacy (no cargado)
├── js/
│   ├── afmp.js / afmp.min.js     # Scripts legacy (no cargados)
│   ├── jquery-2.1.1.js           # jQuery legacy (no cargado)
│   ├── modernizr.js              # Modernizr legacy (no cargado)
│   └── velocity.min.js           # Velocity.js legacy (no cargado)
└── img/
    ├── pictau-icon.png           # Icono en la página de ajustes
    └── cd-icon-close.svg         # Icono legacy
```

Los archivos de `css/` y `js/` son restos de versiones anteriores del plugin (modal animado a pantalla completa) y no se cargan en ninguna parte del sitio actualmente.

---

## Dependencias

- **WordPress** 5.8+ (requiere `do_blocks()` y `WP_HTML_Tag_Processor`)
- **Tema pictau**: la función `wp_svg_inline_filter()` y `get_svg()` deben estar disponibles (definidas en `theme/inc/template-functions.php`)

---

## Instalación

1. Subir la carpeta del plugin a `wp-content/plugins/`.
2. Asegurarse de que la carpeta se llama exactamente `pictau-blocks-gutenberg`.
3. Activar el plugin desde el panel de plugins de WordPress.

---

## Changelog

### 4.0.2
- Fix: el regex que eliminaba `<p>` vacíos en `get_pct_block()` ahora excluye `<p role="status">` para preservar el elemento de accesibilidad que Contact Form 7 genera en el `screen-reader-response`. Sin este fix, CF7 6.1.x lanzaba `TypeError: Cannot set properties of null (setting 'innerText')` al enviar el formulario.

---

## Uso típico en menús de navegación

1. Crear un nuevo Pictau Block en **Pictau Blocks > Añadir nuevo**, editar su contenido con Gutenberg y publicarlo.
2. Copiar el shortcode que aparece en la columna de la lista (`[pictau-blocks id="XX"]`).
3. En **Apariencia > Menús**, añadir un ítem de menú de tipo "Enlace personalizado" con el shortcode como texto del ítem (o en el campo de descripción, según soporte del tema).
4. El tema renderizará el shortcode al generar el HTML del menú.
