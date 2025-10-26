<?php
defined('ABSPATH') || exit;
$action = home_url('/'); // puedes dejarlo así; la redirección ya se encarga del “sin resultados”
?>
<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url($action); ?>">
  <label class="screen-reader-text" for="woocommerce-product-search-field">Buscar:</label>
  <input type="search" id="woocommerce-product-search-field" class="search-field"
         placeholder="Buscar productos…" value="<?php echo get_search_query(); ?>" name="s" />
  <input type="hidden" name="post_type" value="product" />
  <button type="submit">Buscar</button>
</form>
