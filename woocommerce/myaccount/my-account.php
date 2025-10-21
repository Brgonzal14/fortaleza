<?php
defined('ABSPATH') || exit;
$current_user = wp_get_current_user();
?>
<div class="fort-acc">
  <header class="fort-acc__header">
    <div class="fort-acc__avatar"><?php echo get_avatar($current_user->ID, 96); ?></div>
    <div class="fort-acc__meta">
      <h1 class="fort-acc__title"><?php esc_html_e('Mi cuenta','woocommerce'); ?></h1>
      <p class="fort-acc__hello">
        <?php printf(esc_html__('Hola, %s','woocommerce'),
          esc_html($current_user->display_name ?: $current_user->user_login)); ?>
      </p>
    </div>
    <div class="fort-acc__actions">
      <a class="fort-btn" href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>">
        <?php esc_html_e('Editar perfil','woocommerce'); ?>
      </a>
      <a class="fort-link" href="<?php echo esc_url(wc_logout_url(get_permalink())); ?>">
        <?php esc_html_e('Cerrar sesión','woocommerce'); ?>
      </a>
    </div>
  </header>

  <div class="fort-acc__grid">
    <?php do_action('woocommerce_account_navigation'); ?>
    <section class="woocommerce-MyAccount-content">
      <?php do_action('woocommerce_account_content'); ?>
    </section>
  </div>
</div>
