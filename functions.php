<?php
/**
 * Fortaleza Astra Child – funciones del tema hijo
 * Adaptado para funcionar con el tema Astra como padre.
 */
if (!defined('ABSPATH')) exit;

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

// Fragment del carrito (badge)
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
  ob_start(); ?>
  <span class="badge" id="cart-count">
    <?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?>
  </span>
  <?php
  $fragments['#cart-count'] = ob_get_clean();
  return $fragments;
});

// Layout personalizado para la ficha de producto
add_action('wp', function () {
  if (!is_product()) return;

  // Limpiar el summary por defecto de Woo
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
  remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

  // Crear nuestro layout de 2 columnas
  add_action('woocommerce_before_single_product_summary', function () { echo '<div class="fort-grid-2col">'; }, 1);
  add_action('woocommerce_before_single_product_summary', function () {
    echo '<div class="fort-info">';
      woocommerce_template_single_title();
      woocommerce_template_single_price();
      woocommerce_template_single_add_to_cart();
      woocommerce_template_single_meta();
      global $post;
      echo '<div class="fort-description">' . apply_filters('the_content', $post->post_content) . '</div>';
    echo '</div>';
  }, 30);
  add_action('woocommerce_before_single_product_summary', function () { echo '</div>'; }, 60);
});

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
  // Tu código de shortcode aquí... (Lo he omitido por brevedad, pero mantenlo como estaba)
  ob_start(); ?>
  <section class="fort-carousel carousel" aria-roledescription="carousel" aria-label="Destacados">
    <div class="slides" id="slides">
      <article class="slide is-active" aria-hidden="false" aria-live="polite">
        <img src="https://i.imgur.com/0TEq5ty.jpeg" alt="Cuatro lobos emergiendo de una fortaleza" loading="eager" decoding="async">
        <div class="slide-content center">
          <h2>Bienvenidos a la fortaleza</h2>
          <p>Atrévete a cruzar las puertas de la fortaleza.</p>
        </div>
      </article>
      </div>
    <button id="prevBtn" class="nav prev" type="button" aria-label="Slide anterior">‹</button>
    <button id="nextBtn" class="nav next" type="button" aria-label="Siguiente slide">›</button>
    <div id="dots" class="dots" role="tablist" aria-label="Paginación"></div>
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
