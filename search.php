<?php
/**
 * Búsquedas – Fortaleza Child
 * - Si la búsqueda es de productos, usa el loop de WooCommerce
 * - Si no, usa el loop normal del tema
 */
defined('ABSPATH') || exit;

// ¿Es una búsqueda de productos?
$pt = get_query_var('post_type');
$is_product_search = (isset($_GET['post_type']) && $_GET['post_type'] === 'product') || $pt === 'product';

// Carga el header habitual (si tienes header-fortaleza.php en la raíz, usa get_header('fortaleza'))
get_header();

if ( $is_product_search ) : ?>
  <main id="primary" class="site-main woocommerce" style="padding: 24px 0;">
    <?php
    // Avisos de Woo (por si hay notices)
    if ( function_exists('woocommerce_output_all_notices') ) {
      woocommerce_output_all_notices();
    }

    if ( have_posts() ) :

      // Inicio loop de productos
      if ( function_exists('woocommerce_product_loop_start') ) {
        woocommerce_product_loop_start();
      } else {
        echo '<ul class="products">';
      }

      while ( have_posts() ) : the_post();
        if ( function_exists('wc_get_template_part') ) {
          wc_get_template_part( 'content', 'product' );
        } else {
          get_template_part( 'content', 'product' );
        }
      endwhile;

      // Fin loop de productos
      if ( function_exists('woocommerce_product_loop_end') ) {
        woocommerce_product_loop_end();
      } else {
        echo '</ul>';
      }

      // Paginación de Woo
      if ( function_exists('woocommerce_pagination') ) {
        woocommerce_pagination();
      }

    else :
      // SIN RESULTADOS: mostramos nuestro override en /woocommerce/loop/no-products-found.php
      if ( function_exists('wc_get_template') ) {
        wc_get_template( 'loop/no-products-found.php' );
      } else {
        echo '<div class="woocommerce-info">No encontramos resultados.</div>';
      }
    endif;
    ?>
  </main>

<?php else : ?>

  <main id="primary" class="site-main" style="padding: 24px 0;">
    <?php if ( have_posts() ) : ?>
      <?php while ( have_posts() ) : the_post(); ?>
        <?php
        // Usa tu partial si lo tienes; si no, el del tema padre
        get_template_part( 'template-parts/content', 'search' );
        ?>
      <?php endwhile; ?>
      <?php the_posts_navigation(); ?>
    <?php else : ?>
      <?php
      // Fallback cuando no hay resultados de páginas/entradas
      if ( locate_template('template-parts/content-none.php') ) {
        get_template_part( 'template-parts/content', 'none' );
      } else {
        echo '<p>No se encontraron resultados.</p>';
      }
      ?>
    <?php endif; ?>
  </main>

<?php endif; ?>

<?php get_footer(); ?>
