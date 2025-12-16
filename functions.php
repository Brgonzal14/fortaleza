<?php
/**
 * Fortaleza Astra Child – funciones del tema hijo
 * Adaptado para funcionar con el tema Astra como padre.
 */
if (!defined('ABSPATH')) exit;

/* === INCLUDES PERSONALIZADOS === */
$fort_form_cotizador = get_stylesheet_directory() . '/form-cotizador.php';
if ( file_exists( $fort_form_cotizador ) ) {
  require_once $fort_form_cotizador;
}

// =======================================================
//  1. ENCOLADO DE ESTILOS Y SCRIPTS (CORRECTO)
// =======================================================
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'astra-theme-css',
    get_template_directory_uri() . '/style.css',
    [],
    '4.6.13'
  );

  $child_style = get_stylesheet_directory() . '/style.css';
  if (file_exists($child_style)) {
    wp_enqueue_style(
      'fortaleza-child',
      get_stylesheet_uri(),
      ['astra-theme-css'],
      filemtime($child_style)
    );
  }

  $hf_css_fs = get_stylesheet_directory() . '/assets/header-footer.css';
  if (file_exists($hf_css_fs)) {
    wp_enqueue_style(
      'fortaleza-header-footer',
      get_stylesheet_directory_uri() . '/assets/header-footer.css',
      ['fortaleza-child'],
      filemtime($hf_css_fs)
    );
  }

  $global_js_fs = get_stylesheet_directory() . '/assets/global.js';
  if (file_exists($global_js_fs)) {
    wp_enqueue_script(
      'fortaleza-global',
      get_stylesheet_directory_uri() . '/assets/global.js',
      ['jquery'],
      filemtime($global_js_fs),
      true
    );
  }
});

// =======================================================
//  2. SOPORTES DEL TEMA (Mantenido)
// =======================================================
add_action('after_setup_theme', function () {
  // Soporte para WooCommerce
  add_theme_support('woocommerce');

  // Logo personalizable
  add_theme_support('custom-logo', [
    'height'      => 200,
    'width'       => 400,
    'flex-height' => true,
    'flex-width'  => true,
  ]);

  // Galería de productos de WooCommerce
  add_theme_support('wc-product-gallery-slider');
}, 1000);

// =======================================================
//  3. FUNCIONALIDADES DE WOOCOMMERCE (Mantenidas)
//  (La mayoría de estas funciones son agnósticas al tema)
// =======================================================

// Desactivar "Productos relacionados"
add_action('init', function () {
  remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
});

// Mostrar avisos de Woo en la ficha de producto
add_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 5);

// Sección de "Recomendados por categoría"
add_action('woocommerce_after_single_product_summary', 'fortaleza_reco_categoria', 25);
function fortaleza_reco_categoria() {
  if (!is_product()) return;
  global $product;
  if (!$product instanceof WC_Product) return;

  $limit = 4; // Puedes ajustar el número de recomendados
  $cat_ids = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'ids']);
  if (empty($cat_ids)) return;

  $q = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => $limit,
    'orderby'        => 'rand',
    'post__not_in'   => [$product->get_id()],
    'tax_query'      => [['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $cat_ids]],
  ]);

  if (!$q->have_posts()) return;

  echo '<section class="fort-reco">';
  echo '  <h2 class="fort-reco__title">Recomendados:</h2>';
  echo '  <ul class="products columns-' . esc_attr($limit) . ' fort-reco__grid">';
  while ($q->have_posts()) {
    $q->the_post();
    wc_get_template_part('content', 'product');
  }
  echo '  </ul>';
  echo '</section>';
  wp_reset_postdata();
}

// Ocultar ordenación y contador en la página de tienda/archivo
add_action('init', function () {
  remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
  remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});
add_action('woocommerce_before_shop_loop', function() {
  if (is_shop() || is_product_category() || is_product_tag()) {
    echo '<div class="shop-toolbar-spacer" aria-hidden="true"></div>';
  }
}, 5);

// =======================================================
//  4. CAROUSEL DE PORTADA (Mantenido)
// =======================================================
add_action('wp_enqueue_scripts', function () {
  if (is_front_page()) {
    wp_enqueue_style(
      'fort-home-carousel',
      get_stylesheet_directory_uri() . '/assets/home-carousel.css',
      [],
      filemtime(get_stylesheet_directory() . '/assets/home-carousel.css')
    );
    wp_enqueue_script(
      'fort-home-carousel',
      get_stylesheet_directory_uri() . '/assets/home-carousel.js',
      [],
      filemtime(get_stylesheet_directory() . '/assets/home-carousel.js'),
      true
    );
  }
}, 20);

// Shortcode [fortaleza_home_slider]
add_shortcode('fortaleza_home_slider', function () {
  ob_start(); ?>
  <section class="fort-carousel carousel" aria-roledescription="carousel" aria-label="Destacados">
    <div class="slides" id="slides">

      <!-- Slide 1 -->
      <article class="slide is-active" aria-hidden="false" aria-live="polite">
        <img
          src="https://i.imgur.com/0TEq5ty.jpeg"
          alt="Cuatro lobos emergiendo de una fortaleza"
          loading="eager" decoding="async">
        <div class="slide-content center">
          <h2>Bienvenidos a la fortaleza</h2>
          <p>Atrévete a cruzar las puertas de la fortaleza.</p>
        </div>
      </article>

      <!-- Slide 2 -->
      <article class="slide" aria-hidden="true" aria-live="polite">
        <img
          src="https://i.imgur.com/nl8kzDC.jpeg"
          alt="Arte del primer bloque — singles destacados"
          loading="lazy" decoding="async">
        <div class="slide-content center">
          <h2>Singles Primer Bloque</h2>

          <!-- BOTÓN -->
          <a href="https://lafortalezadelahermandad.com/categoria/mitos-y-leyendas/singles-primer-bloque/"
            class="btn primary"
            aria-label="Ver ahora">
            Ver ahora
          </a>
        </div>
</article>


    </div>

    <button id="prevBtn" class="nav prev" type="button" aria-label="Slide anterior">‹</button>
    <button id="nextBtn" class="nav next" type="button" aria-label="Siguiente slide">›</button>
    <div id="dots" class="dots" role="tablist" aria-label="Paginación"></div>
    </section>

  <section class="fort-home-banner">
    <img 
      src="https://lafortalezadelahermandad.com/wp-content/uploads/2025/12/zeromulligan.jpg" 
      alt="Nueva Colección - Zero Mulligan"
      width="1920" height="400"
      loading="lazy"
      decoding="async"
    >
    
    <a class="fort-home-banner__cta"
     href="https://lafortalezadelahermandad.com/categoria/accesorios/zeromulligan/"
     aria-label="Ver productos Zero Mulligan">
    Ver ahora
    </a>
  </section>
  <?php
  return ob_get_clean();
});



// =======================================================
//  5. FORMULARIOS DE REGISTRO E INGRESO (Mantenido)
// =======================================================

// Añadir campos de Nombre y Apellido al registro
add_action('woocommerce_register_form', function () {
  // Tu código para mostrar los campos...
});
add_action('woocommerce_register_post', function ($username, $email, $errors) {
  // Tu código de validación...
}, 10, 3);
add_action('woocommerce_created_customer', function ($customer_id) {
  // Tu código para guardar los campos...
});

// Lógica para páginas separadas de Ingresar y Registrarse
add_filter('woocommerce_registration_enabled', function ($enabled) {
  if (is_page('ingresar')) return false;
  if (is_page('registrarse')) return true;
  return $enabled;
});

// CSS inline para centrar formularios
add_action('wp_enqueue_scripts', function () {
  // <-- CORREGIDO: El fallback ahora es 'astra-theme-css'
  $handle = wp_style_is('fortaleza-header-footer', 'enqueued') ? 'fortaleza-header-footer' : (wp_style_is('fortaleza-child', 'enqueued') ? 'fortaleza-child' : 'astra-theme-css');

  if (is_page('ingresar')) {
    wp_add_inline_style($handle, '
      .woocommerce-account .u-column2{ display:none!important; }
      .woocommerce-account .u-column1{ float:none!important; width:100%!important; max-width:560px; margin:0 auto!important; }
    ');
  }
  if (is_page('registrarse')) {
    wp_add_inline_style($handle, '
      .woocommerce-account .u-column1{ display:none!important; }
      .woocommerce-account .u-column2{ float:none!important; width:100%!important; max-width:560px; margin:0 auto!important; }
    ');
  }
}, 120);

// Redirigir si el usuario ya está logueado
add_action('template_redirect', function() {
  if (is_user_logged_in() && (is_page('ingresar') || is_page('registrarse'))) {
    wp_safe_redirect(wc_get_page_permalink('myaccount'));
    exit;
  }
});


// =======================================================
//  6. CARGAR HEADER Y FOOTER PERSONALIZADOS (Añadido)
// =======================================================
add_action('astra_header', 'fortaleza_cargar_header_personalizado');
function fortaleza_cargar_header_personalizado() {
    get_template_part('template-parts/header', 'fortaleza');
}

add_action('astra_footer', 'fortaleza_cargar_footer_personalizado');
function fortaleza_cargar_footer_personalizado() {
    get_template_part('template-parts/footer', 'fortaleza');
}



/**
 * Mi Cuenta — reducir y renombrar navegación
 */
add_filter('woocommerce_account_menu_items', function ($items) {
    // Construimos un menú limpio con el orden que queremos
    $new = [];

    if (isset($items['orders']))        { $new['orders']        = __('Mis pedidos', 'fortaleza'); }
    if (isset($items['edit-address']))  { $new['edit-address']  = __('Mis direcciones', 'fortaleza'); }
    if (isset($items['edit-account']))  { $new['edit-account']  = __('Detalles de mi cuenta', 'fortaleza'); }

    // Devolvemos SOLO estos tres. (Esto elimina 'dashboard', 'downloads', 'customer-logout', etc.)
    return $new;
}, 999);
/**
 * (Opcional) Ajustar títulos internos de cada endpoint
 * (aparece en algunas plantillas/temas arriba del contenido).
 */
add_filter('woocommerce_endpoint_orders_title',        fn() => __('Mis pedidos', 'fortaleza'));
add_filter('woocommerce_endpoint_edit-address_title',  fn() => __('Mis direcciones', 'fortaleza'));
add_filter('woocommerce_endpoint_edit-account_title',  fn() => __('Detalles de mi cuenta', 'fortaleza'));

// Ocultar formulario de cupones solo en la página del carrito
add_filter('woocommerce_coupons_enabled', function ($enabled) {
  if ( is_cart() ) { return false; } // en Checkout seguirá activo
  return $enabled;
});

// Cambios de strings SOLO en el carrito
add_filter('gettext', function ($translated, $text, $domain) {
  if (function_exists('is_cart') && is_cart() && $domain === 'woocommerce') {

    // 1) Encabezado de la tabla
    if ($text === 'Product' || $text === 'Producto') {
      return 'Productos:'; // con dos puntos
    }

    // 3) Título del bloque de totales
    if ($text === 'Cart totals' || $text === 'Totales del carrito') {
      return 'Precio final:';
    }
  }
  return $translated;
}, 10, 3);

// Carrito: actualizar automáticamente al cambiar cantidades (+, -, o escribir)
add_action('wp_footer', function () {
  if ( ! is_cart() ) return; ?>
  <script>
  (function(){
    let timer;
    function scheduleUpdate(){
      clearTimeout(timer);
      timer = setTimeout(function(){
        const btn = document.querySelector('.cart-update-center button[name="update_cart"], button[name="update_cart"]');
        if(btn){
          // Por si algún tema lo deshabilita hasta que hay cambios
          btn.removeAttribute('disabled');
          btn.click();
        }
      }, 600); // debounce: espera 0.6s por si el usuario sigue presionando
    }

    // Escribir en el input
    document.addEventListener('input', function(e){
      if (e.target && e.target.classList && e.target.classList.contains('qty')) {
        scheduleUpdate();
      }
    });

    // Click en los botones +/–
    document.addEventListener('click', function(e){
      if (e.target && e.target.classList && e.target.classList.contains('qty-btn')) {
        scheduleUpdate();
      }
    });
  })();
  </script>
<?php });

/** =========================
 * BÚSQUEDA DE PRODUCTOS (MEJORADA: INSENSIBLE A TILDES)
 * - Busca por título, descripción, extracto y SKU
 * - Ignora tildes (ej: "limon" encuentra "limón" y viceversa)
 * ========================= */
add_filter('posts_search', function($search, \WP_Query $q){
  if (is_admin() || !$q->is_search()) return $search;
  if (!in_array($q->get('post_type'), ['product', 'any', null, false], true)) return $search;

  global $wpdb;
  $s = trim($q->get('s'));
  if ($s === '') return $search;

  // 1. Mapa de caracteres: vocales (con/sin tilde) -> Grupo Regex
  // Esto hace que 'a' busque [aáà...], y 'á' también busque [aáà...]
  $map = [
      'a' => '[aáàäâ]', 'e' => '[eéèëê]', 'i' => '[iíìïî]', 'o' => '[oóòöô]', 'u' => '[uúùüû]', 'n' => '[nñ]',
      'A' => '[aáàäâ]', 'E' => '[eéèëê]', 'I' => '[iíìïî]', 'O' => '[oóòöô]', 'U' => '[uúùüû]', 'N' => '[nñ]',
      'á' => '[aáàäâ]', 'é' => '[eéèëê]', 'í' => '[iíìïî]', 'ó' => '[oóòöô]', 'ú' => '[uúùüû]', 'ñ' => '[nñ]',
      'Á' => '[aáàäâ]', 'É' => '[eéèëê]', 'Í' => '[iíìïî]', 'Ó' => '[oóòöô]', 'Ú' => '[uúùüû]', 'Ñ' => '[nñ]'
  ];

  // 2. Construir el patrón Regex letra por letra
  $s_clean = '';
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
      $char = mb_substr($s, $i, 1);
      if (isset($map[$char])) {
          $s_clean .= $map[$char]; // Reemplazo (ej: 'o' -> '[oóòöô]')
      } else {
          // Escapar caracteres especiales de Regex (., *, +, etc) para evitar errores
          if (strpos('.*+?^$[]()|\\', $char) !== false) {
              $s_clean .= '\\' . $char;
          } else {
              $s_clean .= $char;
          }
      }
  }

  // 3. Consulta SQL usando REGEXP
  // (REGEXP ya busca coincidencias parciales, no necesita los %)
  $search  = $wpdb->prepare(
    " AND ( {$wpdb->posts}.post_title   REGEXP %s
         OR {$wpdb->posts}.post_content REGEXP %s
         OR {$wpdb->posts}.post_excerpt REGEXP %s
         OR EXISTS (
              SELECT 1 FROM {$wpdb->postmeta} pm
              WHERE pm.post_id = {$wpdb->posts}.ID
                AND pm.meta_key = '_sku'
                AND pm.meta_value REGEXP %s
            )
        ) ",
    $s_clean, $s_clean, $s_clean, $s_clean
  );
  
  return $search;
}, 20, 2);

/** =========================
 *  FRAGMENTOS AJAX DEL MINI-CARRITO
 *  - Refresca el contenido y el badge
 *  ========================= */
add_filter('woocommerce_add_to_cart_fragments', function($fragments){
  // 1) Contenido del mini-cart (Woo imprime mensaje vacío cuando corresponde)
  ob_start();
  woocommerce_mini_cart();
  $fragments['div.widget_shopping_cart_content'] = ob_get_clean();

  // 2) Badge del carrito (contador)
  ob_start();
  $count = ( function_exists('WC') && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
  echo '<span class="badge" id="cart-count">'. intval($count) .'</span>';
  $fragments['#cart-count'] = ob_get_clean();

  return $fragments;
});


/* === [fortaleza_search] – Resultados arriba, recomendados abajo (sin barra lateral ni “Buscar”) === */
add_shortcode('fortaleza_search', function () {
  $term  = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
  $limit = 24;
  $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

  ob_start(); ?>
  <section class="fort-search">
    <div class="fort-search__container">

      <header class="fort-search__head">
        <?php if ($term !== ''): ?>
          <h1>Resultados</h1>
        <?php endif; ?>
      </header>

      <?php
      $have_results = false;

      if ($term !== '') {
        $args = [
          'post_type'      => 'product',
          'post_status'    => 'publish',
          'posts_per_page' => $limit,
          'paged'          => $paged,
          's'              => $term,
          'orderby'        => 'relevance',
        ];
        $q = new WP_Query($args);

        if ($q->have_posts()) {
          $have_results = true; ?>
          <ul class="products columns-4 fort-search__results">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
              <?php wc_get_template_part('content', 'product'); ?>
            <?php endwhile; ?>
          </ul>
          <?php
          echo paginate_links([
            'total'   => (int) $q->max_num_pages,
            'current' => $paged ?: 1,
          ]);
          wp_reset_postdata();
        }
      }

      if (!$have_results) : ?>
        <div class="fort-search__empty">
          <h2>No encontramos resultados.</h2>
          <p>Intenta con otras palabras o explora estas recomendaciones.</p>
        </div>
      <?php endif; ?>

      <section class="fort-search__reco">
        <h3>Recomendados</h3>
        <?php echo do_shortcode('[products limit="8" columns="4" orderby="rand"]'); ?>
      </section>

    </div>
  </section>
  <?php return ob_get_clean();
});

// Modificar el formulario de búsqueda de productos para que siempre apunte a /buscar/
add_filter('get_product_search_form', function($form) {
    $buscar_url = home_url('/buscar/');
    $form = preg_replace('/action="[^"]+"/', 'action="' . esc_url($buscar_url) . '"', $form);
    return $form;
});

/**
 * ❶ Redirige cualquier búsqueda a /buscar/ manteniendo los parámetros (?s=..., etc.)
 */
add_action('template_redirect', function () {
    if (is_admin() || !is_search()) return;

    $page = get_page_by_path('buscar');
    if (!$page) return;

    // Destino = permalink de "Buscar" + query actual
    $dest = add_query_arg($_GET, get_permalink($page->ID));

    // Evita bucles si ya estamos en /buscar/
    $req_path    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $target_path = rtrim(parse_url($dest, PHP_URL_PATH), '/');

    if ($req_path !== $target_path) {
        nocache_headers();
        wp_safe_redirect($dest, 302);
        exit;
    }
});

/**
 * ❷ En /buscar/?s=..., convierte el main query en "la página Buscar"
 *    (para que se renderice tu contenido + shortcode en vez de la plantilla de búsqueda).
 */
add_action('pre_get_posts', function (\WP_Query $q) {
    if (is_admin() || !$q->is_main_query()) return;

    $page = get_page_by_path('buscar');
    if (!$page) return;

    $req_path    = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $buscar_path = trim(parse_url(get_permalink($page->ID), PHP_URL_PATH), '/');

    if ($req_path === $buscar_path) {
        // Forzamos "query de página"
        $q->set('post_type', 'page');
        $q->set('page_id',   $page->ID);
        $q->set('pagename',  get_page_uri($page->ID));
        $q->set('s', '');            // neutraliza la búsqueda del main query
        $q->is_search   = false;
        $q->is_page     = true;
        $q->is_singular = true;
    }
});

/**
 * ❸ Asegura que todos los formularios de búsqueda de productos apunten a /buscar/
 *    (por si algún widget/plantilla usa el form de Woo).
 */
add_filter('get_product_search_form', function ($form) {
    $form = preg_replace('/action="[^"]+"/', 'action="' . esc_url(home_url('/buscar/')) . '"', $form);
    return $form;
});

// Agrega una clase al body cuando estamos en /buscar/
add_filter('body_class', function($classes){
  if (is_page('buscar')) { $classes[] = 'fort-buscar'; }
  return $classes;
});

/**
 * Cambiar el endpoint (pestaña) por defecto de "Mi Cuenta"
 * ya que 'dashboard' fue eliminado del menú.
 */
add_filter('woocommerce_account_get_default_endpoint', function ($endpoint) {
    // Redirige a 'orders' (Mis pedidos) que sí existe en tu menú.
    return 'orders';
}, 99, 1);

// 🔹 Eliminar campo de código postal en el checkout (solo para Chile)
add_filter('woocommerce_checkout_fields', function($fields){
  unset($fields['billing']['billing_postcode']);
  unset($fields['shipping']['shipping_postcode']);
  return $fields;
});

// 🔹 No exigir el código postal como obligatorio
add_filter('woocommerce_billing_fields', function($fields){
  if (isset($fields['billing_postcode'])) {
    $fields['billing_postcode']['required'] = false;
  }
  return $fields;
});
add_filter('woocommerce_shipping_fields', function($fields){
  if (isset($fields['shipping_postcode'])) {
    $fields['shipping_postcode']['required'] = false;
  }
  return $fields;
});

// 🔹 Forzar traducciones personalizadas del checkout
add_filter('gettext', function($translated_text, $text, $domain) {

  $replacements = [
    'Billing details'     => 'Detalles de facturación',
    'Shipping details'    => 'Detalles de envío',
    'Place order'         => 'Realizar pedido',
    'First name'          => 'Nombre',
    'Last name'           => 'Apellido',
    'Company name'        => 'Empresa (opcional)',
    'Street address'      => 'Dirección',
    'Town / City'         => 'Ciudad',
    'State / County'      => 'Región / Provincia',
    'Postcode / ZIP'      => 'Código postal',
    'Phone'               => 'Teléfono',
    'Email address'       => 'Correo electrónico',
    'Your order'          => 'Tu pedido',
    'Order notes'         => 'Notas del pedido',
  ];

  if (isset($replacements[$text])) {
    return $replacements[$text];
  }

  return $translated_text;
}, 999, 3);

/* ====== Checkout: Chile sin Código Postal (Blocks + clásico) ====== */

// 1) En Chile, el postcode NO es obligatorio y se oculta
add_filter('woocommerce_get_country_locale', function($locale){
  if (isset($locale['CL'])) {
    $locale['CL']['postcode']['required'] = false;
    $locale['CL']['postcode']['hidden']   = true;   // lo oculta en el formulario
  }
  return $locale;
}, 20);

// Fallback global por si algún plugin ignora el locale anterior
add_filter('woocommerce_default_address_fields', function($fields){
  if (isset($fields['postcode'])) {
    $fields['postcode']['required'] = false;
    $fields['postcode']['hidden']   = true;
  }
  // Cambiar label/placeholder de Address_2
  if (isset($fields['address_2'])) {
    $fields['address_2']['label']       = 'Departamento';
    $fields['address_2']['placeholder'] = 'Departamento';
  }
  return $fields;
}, 20);

/* ====== Traducciones forzadas (Blocks) ====== */
add_filter('gettext', function($translated, $text, $domain){

  // Línea plegable encima de "address_2" en Woo Blocks
  $map = [
    'Add apartment, suite, unit, etc.' => 'Añadir departamento',
    'Apartment, suite, unit, etc.'     => 'Departamento',
    'Postcode / ZIP'                   => 'Código postal', // por si aparece en algún lugar
  ];

  if (isset($map[$text])) return $map[$text];
  return $translated;
}, 999, 3);

/* ============================
   Regiones / Provincias WooCommerce
   Chile (CL) + Argentina (AR)
============================= */
add_filter( 'woocommerce_states', 'fortaleza_regiones_cl_ar' );
function fortaleza_regiones_cl_ar( $states ) {

    // ===== CHILE =====
    $states['CL'] = array(
        'RM'  => 'Región Metropolitana',
        'I'   => 'Tarapacá',
        'II'  => 'Antofagasta',
        'III' => 'Atacama',
        'IV'  => 'Coquimbo',
        'V'   => 'Valparaíso',
        'VI'  => 'O’Higgins',
        'VII' => 'Maule',
        'VIII'=> 'Biobío',
        'IX'  => 'La Araucanía',
        'X'   => 'Los Lagos',
        'XI'  => 'Aysén',
        'XII' => 'Magallanes',
        'XIV' => 'Los Ríos',
        'XV'  => 'Arica y Parinacota',
        'XVI' => 'Ñuble',
    );

    // ===== ARGENTINA =====
    // Códigos basados en ISO 3166-2:AR (los que usa Woo normalmente)
    $states['AR'] = array(
        'C' => 'Ciudad Autónoma de Buenos Aires',
        'B' => 'Buenos Aires',
        'K' => 'Catamarca',
        'H' => 'Chaco',
        'U' => 'Chubut',
        'X' => 'Córdoba',
        'W' => 'Corrientes',
        'E' => 'Entre Ríos',
        'P' => 'Formosa',
        'Y' => 'Jujuy',
        'L' => 'La Pampa',
        'F' => 'La Rioja',
        'M' => 'Mendoza',
        'N' => 'Misiones',
        'Q' => 'Neuquén',
        'R' => 'Río Negro',
        'A' => 'Salta',
        'J' => 'San Juan',
        'D' => 'San Luis',
        'Z' => 'Santa Cruz',
        'S' => 'Santa Fe',
        'G' => 'Santiago del Estero',
        'V' => 'Tierra del Fuego',
        'T' => 'Tucumán',
    );

    return $states;
}

/* ============================
   Desactivar Select2 (selectWoo)
   para país / región
============================= */
add_action( 'wp_enqueue_scripts', function () {
    // Woo usa este handle en versiones recientes
    wp_dequeue_script( 'selectWoo' );
    wp_deregister_script( 'selectWoo' );

    // Algunos temas/plugins todavía usan estos nombres
    wp_dequeue_style( 'select2' );
    wp_deregister_style( 'select2' );
    wp_dequeue_style( 'select2-css' );
}, 100);

add_action( 'wp_head', function () { ?>
  <style>
    .woocommerce-account select,
    .woocommerce-checkout select {
      pointer-events: auto !important;
    }
  </style>
<?php });

/* ============================
 * Labels básicos de dirección
 * - country  => "País"
 * - state    => "Región"
 * Se aplica a facturación y envío
 * ============================ */
add_filter( 'woocommerce_default_address_fields', 'fortaleza_labels_address', 30 );
function fortaleza_labels_address( $fields ) {

    // País
    if ( isset( $fields['country'] ) ) {
        $fields['country']['label']       = 'País';
        $fields['country']['placeholder'] = 'Selecciona un país/región...';
    }

    // Región / Provincia
    if ( isset( $fields['state'] ) ) {
        $fields['state']['label']       = 'Región';
        $fields['state']['placeholder'] = 'Elige una opción...';
    }

    return $fields;
}

/* =========================================================
   FIX: Forzar etiqueta "País" (Solución definitiva)
   Sobrescribe cualquier plugin que le esté poniendo "Región"
   ========================================================= */
add_filter( 'woocommerce_billing_fields', 'fortaleza_fix_country_label_final', 10000 );
add_filter( 'woocommerce_shipping_fields', 'fortaleza_fix_country_label_final', 10000 );

function fortaleza_fix_country_label_final( $fields ) {
    // Corregir en Facturación
    if ( isset( $fields['billing_country'] ) ) {
        $fields['billing_country']['label'] = 'País';
    }
    
    // Corregir en Envío
    if ( isset( $fields['shipping_country'] ) ) {
        $fields['shipping_country']['label'] = 'País';
    }
    
    return $fields;
}

// Cambiar "Detalles de facturación" por "Detalles de envío" en el checkout
add_filter( 'gettext', 'fortaleza_rename_billing_details_title', 20, 3 );
function fortaleza_rename_billing_details_title( $translated, $text, $domain ) {

    // Solo para WooCommerce y en la página de finalizar compra
    if ( 'woocommerce' === $domain && is_checkout() ) {

        if ( 'Detalles de facturación' === $translated ) {
            $translated = 'Detalles de envío';
        }
    }

    return $translated;
}

add_filter( 'big_image_size_threshold', '__return_false' );

/* Desactivar por defecto "enviar a una dirección diferente" en el checkout */
add_filter( 'woocommerce_shipping_address_enabled', '__return_false' );
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

function fortaleza_change_billing_details_heading( $translated_text, $text, $domain ) {

    // Solo tocar textos de WooCommerce
    if ( 'woocommerce' !== $domain ) {
        return $translated_text;
    }

    // Caso 1: texto original en inglés
    if ( 'Billing details' === $text ) {
        return 'Detalles del envío';
    }

    // Caso 2: texto ya traducido al español
    if ( 'Detalles de facturación' === $translated_text ) {
        return 'Detalles del envío';
    }

    return $translated_text;
}
add_filter( 'gettext', 'fortaleza_change_billing_details_heading', 20, 3 );

// Asegurar que siempre exista billing_phone y que sea obligatorio
add_filter( 'woocommerce_checkout_fields', function( $fields ) {

    // Si existe el campo de teléfono de facturación, le cambiamos la etiqueta
    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['label']   = 'Teléfono de contacto para el envío';
        $fields['billing']['billing_phone']['required'] = true;
    }

    return $fields;
}, 20 );

// Copiar el teléfono visible al billing_phone si viene vacío
add_action( 'woocommerce_checkout_process', function() {

    // Si billing_phone viene vacío pero existe algún otro campo de teléfono, lo copiamos.
    if ( empty( $_POST['billing_phone'] ) ) {

        // Ejemplo: si tu plugin está usando shipping_phone
        if ( ! empty( $_POST['shipping_phone'] ) ) {
            $_POST['billing_phone'] = sanitize_text_field( $_POST['shipping_phone'] );
        }

        // Si usas otro nombre de campo, puedes replicar esto cambiando la clave:
        // if ( ! empty( $_POST['mi_campo_telefono'] ) ) {
        //     $_POST['billing_phone'] = sanitize_text_field( $_POST['mi_campo_telefono'] );
        // }
    }
} );

// Ocultar automáticamente los avisos de WooCommerce en Mi cuenta
add_action( 'wp_footer', 'lfh_auto_hide_wc_notices' );
function lfh_auto_hide_wc_notices() {

    // Solo en la página de Mi cuenta (puedes quitar esto si lo quieres en todas)
    if ( ! is_account_page() ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.querySelector('.woocommerce-account .woocommerce-notices-wrapper');
        if (!wrapper) return;

        // Espera 6 segundos (6000 ms). Cambia a 8000 para 8 segundos si quieres.
        setTimeout(function () {
            wrapper.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(-10px)';

            // Después de la animación, lo ocultamos completamente
            setTimeout(function () {
                wrapper.style.display = 'none';
            }, 700);
        }, 6000); // <- aquí ajustas 6000 (6 s) o 8000 (8 s)
    });
    </script>
    <?php
}

// Mover productos sin stock al final del catálogo
// y ordenar del más caro al más barato
add_filter( 'posts_clauses', 'ordenar_agotados_al_final', 2000, 2 );
function ordenar_agotados_al_final( $clauses, $query ) {

    // Solo en catálogo (tienda, categorías, etiquetas) y no en admin ni búsquedas
    if ( ! is_admin()
        && $query->is_main_query()
        && ( is_shop() || is_product_category() || is_product_tag() )
    ) {
        global $wpdb;

        // JOIN para estado de stock
        $clauses['join'] .= "
            LEFT JOIN {$wpdb->postmeta} AS stockstatus
                ON {$wpdb->posts}.ID = stockstatus.post_id
               AND stockstatus.meta_key = '_stock_status'
        ";

        // JOIN para el precio
        $clauses['join'] .= "
            LEFT JOIN {$wpdb->postmeta} AS price_meta
                ON {$wpdb->posts}.ID = price_meta.post_id
               AND price_meta.meta_key = '_price'
        ";

        // ORDER BY:
        // 1) stockstatus: primero 'instock', luego 'outofstock'
        // 2) precio: más caro -> más barato (DESC)
        $clauses['orderby'] = "
            stockstatus.meta_value ASC,
            CAST(price_meta.meta_value AS DECIMAL(10,2)) DESC
        ";
    }

    return $clauses;
}

/**
 * Convertir pagos de PayPal de CLP a USD
 * y evitar CURRENCY_NOT_SUPPORTED
 *
 * Asume que la tienda trabaja en CLP.
 * Ajusta la tasa según el valor del dólar.
 */
add_filter( 'woocommerce_paypal_args', 'fortaleza_convertir_clp_a_usd_para_paypal', 20, 2 );

function fortaleza_convertir_clp_a_usd_para_paypal( $args, $order ) {

    // ***** AJUSTA ESTA TASA CUANDO CAMBIE EL DÓLAR *****
    // 1 USD = 1000 CLP  ->  1 CLP = 0.001 USD
    $clp_a_usd = 0.001;

    // Forzar moneda USD para PayPal
    $args['currency_code'] = 'USD';

    // Campos de montos que normalmente van en la petición de PayPal
    $campos_montos = array(
        'amount',               // total del carrito (para pagos simples)
        'shipping',             // envío
        'handling_cart',        // cargos extra
        'tax_cart',             // impuestos
        'discount_amount_cart', // descuentos
    );

    foreach ( $campos_montos as $campo ) {
        if ( isset( $args[ $campo ] ) && $args[ $campo ] !== '' ) {
            $monto_clp           = floatval( $args[ $campo ] );
            $monto_usd           = round( $monto_clp * $clp_a_usd, 2 ); // 2 decimales para USD
            $args[ $campo ]      = $monto_usd;
        }
    }

    // También convertir los ítems lineales (productos) si existen
    // PayPal los envía como amount_1, amount_2, etc.
    foreach ( $args as $key => $value ) {
        if ( strpos( $key, 'amount_' ) === 0 ) {
            $monto_clp      = floatval( $value );
            $monto_usd      = round( $monto_clp * $clp_a_usd, 2 );
            $args[ $key ]   = $monto_usd;
        }
    }

    return $args;
}
