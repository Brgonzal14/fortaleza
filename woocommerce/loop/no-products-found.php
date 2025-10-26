<?php
defined('ABSPATH') || exit;
/** WooCommerce: loop/no-products-found.php (override del theme) */
$q = get_search_query();
?>
<section class="wc-no-results" style="padding:32px 0">
  <div class="container" style="max-width:1240px;margin:0 auto;padding:0 16px">
    <h1 style="margin:0 0 10px">
      <?php echo $q ? 'No encontramos “'.esc_html($q).'”.' : 'No encontramos resultados.'; ?>
    </h1>
    <p style="opacity:.85;margin:0 0 20px">
      Intenta con otras palabras o revisa estas recomendaciones.
    </p>

    <?php if ( function_exists('get_product_search_form') ) { get_product_search_form(); } ?>

    <h3 style="margin:24px 0 12px">Recomendados</h3>
    <?php echo do_shortcode('[products limit="8" columns="4" orderby="rand"]'); ?>
  </div>
</section>
