<?php
defined('ABSPATH') || exit;
$items = wc_get_account_menu_items(); // Ya viene filtrado por tu functions.php
?>
<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e('Navegación de la cuenta','woocommerce'); ?>">
  <ul class="fort-acc__nav">
    <?php 
    // Obtenemos el enlace de la página "Mi cuenta" UNA SOLA VEZ
    $my_account_url = wc_get_page_permalink('myaccount');

    foreach ($items as $endpoint => $label): 
      
      // LÓGICA DE ENLACE MEJORADA:
      // Si el endpoint es 'dashboard' (tu "Inicio"), usa el enlace base.
      // Si no, usa la función normal para endpoints (ej. /orders, /edit-address)
      $link_url = ($endpoint === 'dashboard') 
        ? $my_account_url 
        : wc_get_endpoint_url($endpoint, '', $my_account_url);
    ?>
      <li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
        
        <a href="<?php echo esc_url($link_url); ?>">
          <span class="nav-label"><?php echo esc_html($label); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>