<?php
/**
 * Single Product – Layout 2 columnas (Fortaleza)
 */

defined('ABSPATH') || exit;
global $product;

do_action('woocommerce_before_single_product');

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('fort-grid-2col', $product); ?>>

	<?php
	// Columna izquierda: galería estándar de Woo
	do_action('woocommerce_before_single_product_summary');
	?>

	<div class="fort-info entry-summary">
		<?php
		// (opcional) quita el “resumen corto” encima de la descripción larga
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

		// Summary estándar (título, precio, add to cart, meta…)
		do_action( 'woocommerce_single_product_summary' );

		// Descripción larga del editor, justo debajo del precio/botón
		echo '<div class="fort-longdesc">';
		the_content();
		echo '</div>';
		?>
	</div>
</div>
<?php do_action('woocommerce_after_single_product'); ?>
