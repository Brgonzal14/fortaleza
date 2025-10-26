<?php
defined('ABSPATH') || exit;
?>
<div class="woocommerce-info" style="background:#141416;border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:16px;">
  <p style="margin:0 0 10px;">No encontramos resultados para tu búsqueda.</p>
  <p style="margin:0 0 10px;">Prueba con otra palabra clave o explora el catálogo:</p>
  <p style="margin:0;">
    <a class="button" href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>">Ir al catálogo</a>
  </p>
</div>
