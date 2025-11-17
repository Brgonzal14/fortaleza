<header class="site-header" role="banner">
  <div class="nav-bar">
    <!-- Logo con fallback -->
    <div class="logo">
      <?php
      if ( function_exists('the_custom_logo') && has_custom_logo() ) {
        the_custom_logo(); // Apariencia > Personalizar > Identidad del sitio
      } else { ?>
        <a class="custom-logo-link" href="<?php echo esc_url( home_url('/') ); ?>">
          <img class="custom-logo"
               src="<?php echo esc_url( get_stylesheet_directory_uri().'/assets/logo.png' ); ?>"
               alt="<?php echo esc_attr__( 'Logo tienda', 'fortaleza' ); ?>">
        </a>
      <?php } ?>
    </div>

    <!-- Grupo central: Catálogo + Buscador -->
    <div class="center-group">
      <!-- Botón Catálogo + Panel -->
      <div class="catalog-trigger">
        <button class="cat-btn" type="button"
                aria-expanded="false" aria-controls="catPanel">
          <span class="cat-ico" aria-hidden="true">
            <!-- ícono hamburguesa -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M3 6h18v2H3V6zm0 5h12v2H3v-2zm0 5h18v2H3v-2z"/>
            </svg>
          </span>
          <span><?php echo esc_html__( 'Catálogo', 'fortaleza' ); ?></span>
        </button>

        <div id="catPanel" class="cat-panel" hidden>
          <ul class="cat-list" role="menu">
<?php
/**
 * Mostrar hijos de varias raíces en el panel Catálogo.
 * Ajusta los slugs de las raíces en $root_slugs.
 */
$root_slugs = ['mitos-y-leyendas', 'accesorios']; // <--- AJUSTA ESTOS SLUGS

// Carga los términos raíz por slug
$roots = array_filter(array_map(function($slug){
  return get_term_by('slug', $slug, 'product_cat');
}, $root_slugs));

// Ordena por nombre (opcional, para que el grupo aparezca ordenado)
usort($roots, function($a, $b){
  return strcasecmp($a->name, $b->name);
});

if ($roots) :
  foreach ($roots as $root) :
    if (! $root) continue;

    // 1) Etiqueta de grupo (no clickeable) — opcional
    echo '<li class="cat-root" role="presentation">'. esc_html($root->name) .'</li>';

    // 2) Primer nivel: hijos del root
    $parents = get_terms([
      'taxonomy'   => 'product_cat',
      'hide_empty' => true,
      'parent'     => (int) $root->term_id,
      'orderby'    => 'name',
      'order'      => 'ASC',
      'number'     => 50,
    ]);

    if (!is_wp_error($parents) && $parents) :
      foreach ($parents as $parent) :
        $plink = get_term_link($parent);
        echo '<li class="cat-item" role="none">';
          echo '<a role="menuitem" href="'. esc_url($plink) .'">'. esc_html($parent->name) .'</a>';

          // 3) Segundo nivel: subcategorías del primer nivel
          $children = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => $parent->term_id,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'number'     => 50,
          ]);

          if (!is_wp_error($children) && $children) {
            echo '<ul class="subcats">';
              foreach ($children as $child) {
                $clink = get_term_link($child);
                echo '<li><a href="'. esc_url($clink) .'">'. esc_html($child->name) .'</a></li>';
              }
            echo '</ul>';
          }
        echo '</li>';
      endforeach;
    endif;

  endforeach;
else :
  echo '<li class="cat-item empty"><em>'. esc_html__('No hay categorías aún','fortaleza') .'</em></li>';
endif;

// 🔹 Enlace fijo a la página de cotización
$cotiza_page = get_page_by_path( 'cotiza-tus-mazos-o-cartas' );
if ( $cotiza_page ) {
  $cotiza_url = get_permalink( $cotiza_page );
  echo '<li class="cat-item cat-cotiza" role="none">';
    echo '<a role="menuitem" href="'. esc_url( $cotiza_url ) .'">'. esc_html__( 'Cotiza tu mazo o las singles que te hacen falta', 'fortaleza' ) .'</a>';
  echo '</li>';
}
?>

          </ul>
        </div>
      </div>
      <!-- /Catálogo -->

      <!-- Buscador -->
<?php
      // Conservamos el término ya buscado (si lo hay)
$qs = isset($_GET['s']) ? wp_unslash($_GET['s']) : get_search_query();
?>
<form class="search"
      role="search"
      method="get"
      action="<?php echo esc_url( home_url('/buscar/') ); ?>"  
      aria-label="<?php echo esc_attr__( 'Buscar productos', 'fortaleza' ); ?>">
  <label for="site-search" class="screen-reader-text">
    <?php echo esc_html__( 'Buscar productos por nombre o SKU', 'fortaleza' ); ?>
  </label>

  <span class="search-icon" aria-hidden="true">🔍</span>

  <input
    type="search"
    id="site-search"
    name="s"
    placeholder="<?php echo esc_attr__( 'Buscar cartas, colecciones o SKU…', 'fortaleza' ); ?>"
    value="<?php echo esc_attr( $qs ); ?>"
    inputmode="search"
    enterkeyhint="search"
    autocomplete="on"
    spellcheck="false"
    required
  >

  <!-- Forzamos búsqueda sólo en productos -->
  <input type="hidden" name="post_type" value="product">

  <button class="search-btn" type="submit">
    <?php echo esc_html__( 'Buscar', 'fortaleza' ); ?>
  </button>
</form>
</div><!-- /.center-group -->

<!-- Acciones derecha -->
<?php
  // URLs de cuenta/login/registro (con fallback si aún no existen las páginas)
  $account_url = wc_get_page_permalink('myaccount');
  $login_url   = get_permalink( get_page_by_path( 'ingresar' ) );
  $reg_url     = get_permalink( get_page_by_path( 'registrarse' ) );

  if ( ! $login_url ) { $login_url = $account_url; }
  if ( ! $reg_url )   { $reg_url   = add_query_arg('register', '1', $account_url); }
?>
<nav class="actions" aria-label="<?php echo esc_attr__( 'Acciones de usuario', 'fortaleza' ); ?>">
  <?php if ( is_user_logged_in() ) : ?>
    <a class="link" href="<?php echo esc_url( $account_url ); ?>">
      <span class="ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <circle cx="12" cy="7" r="4" fill="currentColor"/>
          <path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="currentColor"/>
        </svg>
      </span>
      <span><?php echo esc_html__( 'Mi cuenta', 'fortaleza' ); ?></span>
    </a>

    <a class="btn" href="<?php echo esc_url( wp_logout_url( home_url('/') ) ); ?>" rel="nofollow">
      <span class="ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
          <path d="M3 3h2v18H3z" fill="currentColor"/>
          <path d="M13 7l5 5-5 5v-3H7v-4h6V7z" fill="currentColor"/>
        </svg>
      </span>
      <span><?php echo esc_html__( 'Salir', 'fortaleza' ); ?></span>
    </a>
  <?php else : ?>
    <a class="link" href="<?php echo esc_url( $login_url ); ?>">
      <span class="ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <circle cx="12" cy="7" r="4" fill="currentColor"/>
          <path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="currentColor"/>
        </svg>
      </span>
      <span><?php echo esc_html__( 'Ingresar', 'fortaleza' ); ?></span>
    </a>

    <a class="btn" href="<?php echo esc_url( $reg_url ); ?>">
      <span class="ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <circle cx="9" cy="8" r="4" fill="currentColor"/>
          <path d="M2 20c0-3.5 3.5-5.5 7-5.5s7 2 7 5.5" fill="currentColor"/>
          <path d="M18 7h-2V5h-2v2h-2v2h2v2h2V9h2z" fill="currentColor"/>
        </svg>
      </span>
      <span><?php echo esc_html__( 'Registrarse', 'fortaleza' ); ?></span>
    </a>
  <?php endif; ?>

  <!-- Carrito -->
<div class="cart-wrap">
  <button class="cart-btn"
          type="button"
          aria-controls="miniCartPanel"
          aria-expanded="false"
          aria-label="<?php echo esc_attr__( 'Abrir mini carrito', 'fortaleza' ); ?>">
    <span class="cart-ico" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true">
        <path d="M7 20a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM3 4h2l2.4 10.2A2 2 0 0 0 9.36 16h8.86a2 2 0 0 0 1.94-1.52L22 7H6.3" fill="currentColor"/>
      </svg>
    </span>
    <span class="badge" id="cart-count">
      <?php echo ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0; ?>
    </span>
  </button>

  <!-- Panel del mini-carrito (Woo lo refresca por AJAX) -->
  <div id="miniCartPanel" class="mini-cart-panel" hidden>
  <div class="widget_shopping_cart_content">
    <?php woocommerce_mini_cart(); ?>
  </div>
</div>
</div>
</nav>
</div>
