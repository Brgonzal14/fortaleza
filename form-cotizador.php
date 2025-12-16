<?php
/**
 * Formulario de cotización de mazos / lotes de cartas
 * Shortcode: [fort_cotizar_mazo]
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Render del formulario
 */
function fort_cotizar_mazo_form_shortcode() {

  // Mensajito después de enviar
  $msg = '';
  if ( isset( $_GET['cotizacion'] ) ) {
    if ( $_GET['cotizacion'] === 'ok' ) {
      $msg = '<div class="fort-cotizador-msg ok">¡Gracias! Tu cotización fue enviada correctamente. Te responderemos por correo.</div>';
    } elseif ( $_GET['cotizacion'] === 'error' ) {
      $msg = '<div class="fort-cotizador-msg error">Hubo un problema al enviar la cotización. Intenta nuevamente.</div>';
    }
  }

  ob_start();
  ?>
  <div class="fort-cotizador-wrap">
    <?php echo $msg; ?>

    <h2 class="fort-cotizador-title">Cotiza tu mazo o lote de cartas</h2>
    <p class="fort-cotizador-intro">
      Envíanos una lista, fotos de tu mazo o capturas con las cartas que quieres 
      y te responderemos con una cotización aproximada.
    </p>

    <form class="fort-cotizador-form"
          action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
          method="post"
          enctype="multipart/form-data">

      <!-- Campo requerido: action para admin-post -->
      <input type="hidden" name="action" value="fort_cotizar_mazo">
      <?php wp_nonce_field( 'fort_cotizar_mazo', 'fort_cotizar_nonce' ); ?>

      <div class="fort-field">
        <label for="fort_name">Nombre<span>*</span></label>
        <input type="text" id="fort_name" name="fort_name" required>
      </div>

      <div class="fort-field">
        <label for="fort_email">Correo electrónico<span>*</span></label>
        <input type="email" id="fort_email" name="fort_email" required>
      </div>

      <div class="fort-field">
        <label for="fort_titulo">Título o tema de tu cotización</label>
        <input type="text" id="fort_titulo" name="fort_titulo" placeholder="Ej: Mazo druida más soporte adicional">
      </div>

      <div class="fort-field">
        <label for="fort_tipo">¿Qué quieres cotizar?</label>
        <select id="fort_tipo" name="fort_tipo">
          <option value="">Selecciona una opción</option>
          <option value="mazo">Mazo completo</option>
          <option value="lote">Lote de cartas sueltas</option>
          <option value="coleccion">Single</option>
          <option value="otro">Otro</option>
        </select>
      </div>

      <div class="fort-field">
        <label for="fort_lista">
          Lista de cartas / detalles
        </label>
        <textarea id="fort_lista" name="fort_lista" rows="6"
          placeholder="Escribe aquí la lista de cartas, edición, cantidad, estado, etc."></textarea>
      </div>

      <div class="fort-field">
        <label for="fort_files">
          Imágenes (mazo, cartas, capturas, etc.)
        </label>
        <input
          type="file"
          id="fort_files"
          name="fort_files[]"
          multiple
          accept="image/*,.pdf">
        <small>Puedes subir varias imágenes (por ejemplo fotos del mazo o capturas de pantalla).</small>
      </div>

      <button type="submit" class="fort-btn-submit">
        Enviar cotización
      </button>
    </form>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode( 'fort_cotizar_mazo', 'fort_cotizar_mazo_form_shortcode' );

/**
 * Handler del envío (admin-post)
 */
function fort_handle_cotizar_mazo_form() {

  if ( ! isset( $_POST['fort_cotizar_nonce'] ) || ! wp_verify_nonce( $_POST['fort_cotizar_nonce'], 'fort_cotizar_mazo' ) ) {
    wp_safe_redirect( add_query_arg( 'cotizacion', 'error', wp_get_referer() ?: home_url() ) );
    exit;
  }

  // Sanitizar campos
  $name   = isset( $_POST['fort_name'] )   ? sanitize_text_field( $_POST['fort_name'] )   : '';
  $email  = isset( $_POST['fort_email'] )  ? sanitize_email( $_POST['fort_email'] )       : '';
  $titulo = isset( $_POST['fort_titulo'] ) ? sanitize_text_field( $_POST['fort_titulo'] ) : '';
  $tipo   = isset( $_POST['fort_tipo'] )   ? sanitize_text_field( $_POST['fort_tipo'] )   : '';
  $lista  = isset( $_POST['fort_lista'] )  ? wp_kses_post( $_POST['fort_lista'] )         : '';

  if ( empty( $name ) || empty( $email ) ) {
    wp_safe_redirect( add_query_arg( 'cotizacion', 'error', wp_get_referer() ?: home_url() ) );
    exit;
  }

  // Montar el contenido del correo
  $subject = 'Nueva cotización de mazo desde la web Fortaleza';
  if ( $titulo ) {
    $subject .= ' - ' . $titulo;
  }

  $body  = "Nueva solicitud de cotización de mazo/lote desde la web Fortaleza:\n\n";
  $body .= "Nombre: {$name}\n";
  $body .= "Correo: {$email}\n";
  $body .= "Tipo de cotización: " . ( $tipo ? $tipo : 'No especificado' ) . "\n";
  $body .= "Título: " . ( $titulo ? $titulo : 'No especificado' ) . "\n\n";
  $body .= "Detalles / lista de cartas:\n";
  $body .= ( $lista ? $lista : 'Sin descripción adicional.' ) . "\n";

  $body .= "\n---\nEnviado desde la página de cotización de Fortaleza.\n";

  // Subir archivos (múltiples)
  $attachments = array();

  if ( ! empty( $_FILES['fort_files'] ) && ! empty( $_FILES['fort_files']['name'][0] ) ) {

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $files = $_FILES['fort_files'];

    foreach ( $files['name'] as $key => $filename ) {

      if ( empty( $filename ) ) {
        continue;
      }

      $file = array(
        'name'     => $files['name'][ $key ],
        'type'     => $files['type'][ $key ],
        'tmp_name' => $files['tmp_name'][ $key ],
        'error'    => $files['error'][ $key ],
        'size'     => $files['size'][ $key ],
      );

      // Opcional: limitar tamaño (ej: 5MB por archivo)
      if ( $file['size'] > 5 * 1024 * 1024 ) {
        continue; // saltar archivos muy grandes
      }

      $uploaded = wp_handle_upload( $file, array( 'test_form' => false ) );

      if ( isset( $uploaded['file'] ) && empty( $uploaded['error'] ) ) {
        $attachments[] = $uploaded['file'];
      }
    }
  }

    // Correos de destino: tienda + correo de empresa
    $to = array(
      'admin@lafortalezadelahermandad.cl',
      'millennialsgames.spa@gmail.com',
    );


  $headers = array(
    'Content-Type: text/plain; charset=UTF-8',
    'Reply-To: ' . $name . ' <' . $email . '>',
  );

  $sent = wp_mail( $to, $subject, $body, $headers, $attachments );

  $status = $sent ? 'ok' : 'error';

  // Redirigir de vuelta a la página con mensaje
  $redirect = wp_get_referer() ?: home_url();
  wp_safe_redirect( add_query_arg( 'cotizacion', $status, $redirect ) );
  exit;
}
add_action( 'admin_post_nopriv_fort_cotizar_mazo', 'fort_handle_cotizar_mazo_form' );
add_action( 'admin_post_fort_cotizar_mazo',        'fort_handle_cotizar_mazo_form' );
