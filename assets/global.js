// Fortaleza - JS global (limpio y unificado)
document.addEventListener('DOMContentLoaded', () => {
  
  /* =============== MINI CARRITO (anclado al botón) =============== */
  const cartBtn   = document.querySelector('.cart-btn');
  const cartPanel = document.getElementById('miniCartPanel');

  /* =============== CATÁLOGO =============== */
  const catBtn   = document.querySelector('.cat-btn');
  const catPanel = document.getElementById('catPanel');

  // Helpers para no superponer paneles
  const closeIf = (panel, btn) => {
    if (panel && !panel.hidden) {
      panel.hidden = true;
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }
  };

 /* ---- Carrito ---- */
if (cartBtn && cartPanel) {
  // el panel se queda donde está en el HTML (fuera del <button>)
  cartPanel.classList.add('cart-anchored');

  const openCart  = () => {
    // cierra catálogo si estuviera abierto (si la helper existe)
    if (typeof closeIf === 'function') { try { closeIf(catPanel, catBtn); } catch(_){} }
    cartPanel.hidden = false;
    cartBtn.setAttribute('aria-expanded', 'true');
  };
  const closeCart = () => {
    cartPanel.hidden = true;
    cartBtn.setAttribute('aria-expanded', 'false');
  };
  const toggleCart = () => (cartPanel.hidden ? openCart() : closeCart());

  cartBtn.addEventListener('click', (e) => {
    e.stopPropagation(); // evita que el doc-listener cierre inmediatamente
    toggleCart();
  });

  // Cerrar al clicar fuera
  document.addEventListener('click', (e) => {
    if (!cartPanel.hidden && !cartPanel.contains(e.target) && !cartBtn.contains(e.target)) {
      closeCart();
    }
  });

  // Cerrar con ESC
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCart(); });

  // --- Helpers ---
  // Fallback para actualizar el badge sumando cantidades del mini-cart
  const fallbackUpdateBadge = () => {
    try {
      const qtyEls = cartPanel.querySelectorAll('.woocommerce-mini-cart .quantity');
      let total = 0;
      qtyEls.forEach(q => {
        // "2 × $15.000" -> 2
        const m = q.textContent.match(/^\s*(\d+)/);
        total += m ? parseInt(m[1], 10) : 1;
      });
      const badge = document.getElementById('cart-count');
      if (badge && Number.isFinite(total)) badge.textContent = String(total);
    } catch(_) {}
  };

  // --- Eventos WooCommerce (requiere jQuery, que Woo ya carga) ---
  if (window.jQuery) {
    const $ = window.jQuery;

    // 1) Abrir el mini-carrito inmediatamente al agregar
    $(document.body).on('added_to_cart', function () {
      openCart();
    });

    // 2) Cuando Woo refresca fragments, nuestro PHP ya reemplaza:
    //    - div.widget_shopping_cart_content (contenido)
    //    - #cart-count (badge)   ← lo añadimos en functions.php
    //    Si por algún motivo el fragment del badge no llegó, aplicamos fallback.
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
      const badge = document.getElementById('cart-count');
      const n = parseInt(badge ? badge.textContent : '', 10);
      if (!badge || Number.isNaN(n)) {
        fallbackUpdateBadge();
      }
    });

    // 3) Por si cambian cantidades desde mini-cart o cart (según plugins)
    $(document.body).on('updated_cart_totals', function () {
      fallbackUpdateBadge();
    });
  }
}

  /* ---- Catálogo ---- */
  if (catBtn && catPanel) {
    const openCat  = () => {
      // cierra carrito si estuviera abierto
      closeIf(cartPanel, cartBtn);
      catPanel.hidden = false;
      catBtn.setAttribute('aria-expanded','true');
    };
    const closeCat = () => {
      catPanel.hidden = true;
      catBtn.setAttribute('aria-expanded','false');
    };
    const toggleCat = () => (catPanel.hidden ? openCat() : closeCat());

    catBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleCat();
    });

    document.addEventListener('click', (e) => {
      if (!catPanel.hidden && !catPanel.contains(e.target) && !catBtn.contains(e.target)) {
        closeCat();
      }
    });

    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCat(); });
  }

  /* =============== FOOTER =============== */
  // Copia de año (si usas PHP date('Y'), puedes quitar este bloque)
  const y = document.getElementById('yearCopy');
  if (y) y.textContent = new Date().getFullYear();

  // Acordeones: inician cerrados (todas las pantallas) y permiten multi-open
  const cols = Array.from(document.querySelectorAll('.site-footer .footer-col'));
  cols.forEach(col => {
    const btn   = col.querySelector('.footer-toggle');
    const panel = col.querySelector('.footer-panel');
    if (!btn || !panel) return;

    // Estado inicial: cerrado SIEMPRE
    col.classList.remove('open');
    panel.hidden = true;
    btn.setAttribute('aria-expanded', 'false');

    // Toggle individual (multi-open)
    btn.addEventListener('click', () => {
      const willOpen = !col.classList.contains('open');
      col.classList.toggle('open', willOpen);
      panel.hidden = !willOpen;
      btn.setAttribute('aria-expanded', String(willOpen));
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const scheduleHide = () => {
    
    // ▼ AÑADE ESTA LÍNEA AQUÍ ▼
    if (document.body.classList.contains('woocommerce-account')) return;

    // El resto de la función sigue igual
    setTimeout(() => {
      document.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error')
        .forEach(el => {
          el.style.transition = 'opacity .3s ease, transform .3s ease';
          el.style.opacity = '0';
          el.style.transform = 'translateY(-4px)';
          setTimeout(() => el.remove(), 400); // lo sacamos del DOM tras la animación
        });
    }, 10000); // 10 segundos
  };

  scheduleHide();

  // Cuando se agrega al carrito via AJAX, vuelve a programar el cierre
  if (window.jQuery) {
    jQuery(document.body).on('added_to_cart wc_fragments_refreshed', scheduleHide);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  // fuerza salto de línea antes de "facturación" o "envío"
  const splitLastWord = (a) => {
    if (!a || a.dataset.split === '1') return;
    const txt = a.textContent.trim();
    // palabras objetivo con acento (ajusta si cambias traducciones)
    const targets = ['facturación', 'envío'];

    for (const word of targets) {
      const idx = txt.toLowerCase().lastIndexOf(word);
      if (idx > 0) {
        const first = txt.slice(0, idx).trim();
        const last  = txt.slice(idx).trim();
        a.innerHTML = `${first}<br>${last}`;
        a.dataset.split = '1';               // evita duplicar
        a.style.whiteSpace = 'normal';       // por si algún estilo lo evita
        break;
      }
    }
  };

  // Aplica a los botones dentro de Direcciones (Añadir/Editar)
  document.querySelectorAll('.woocommerce-Address .edit a').forEach(splitLastWord);

  // Por si Woo actualiza dinámicamente (AJAX), observa cambios
  const obs = new MutationObserver(() => {
    document.querySelectorAll('.woocommerce-Address .edit a').forEach(splitLastWord);
  });
  obs.observe(document.body, { childList: true, subtree: true });
});


// Cantidad: botones +/– con eliminación al llegar a cero y límite de stock
(function ($) {
  if (!$('body').hasClass('woocommerce-cart')) return;

  function enhanceQty($scope) {
    $scope.find('td.product-quantity .quantity').each(function () {
      const $box = $(this);
      if ($box.data('enhanced') === 1) return; // Evita duplicados

      const $input = $box.find('.qty');
      if (!$input.length) return;

      // 1. FIX: Si el input está oculto (stock 1), lo forzamos a ser texto visible
      if ($input.attr('type') === 'hidden') {
        $input.attr('type', 'text');
        $input.prop('readonly', true);
      }

      const $minus = $('<button type="button" class="qty-btn qty-minus" aria-label="Disminuir">–</button>');
      const $plus  = $('<button type="button" class="qty-btn qty-plus"  aria-label="Incrementar">+</button>');

      $minus.insertBefore($input);
      $plus.insertAfter($input);
      $box.data('enhanced', 1);

      // Lectura de límites
      const step = parseFloat($input.attr('step')) || 1;
      const min  = parseFloat($input.attr('min')); // Generalmente es 1
      const max  = parseFloat($input.attr('max')); // Stock máximo

      const num = () => parseFloat(($input.val() + '').replace(',', '.')) || 0;

      // Función mensaje Toast (Límite alcanzado)
      const showLimitMsg = () => {
        if ($('.fort-toast').length) return;
        const msg = $('<div class="fort-toast">¡No puedes añadir más unidades de este producto!</div>');
        $('body').append(msg);
        setTimeout(() => msg.addClass('show'), 10);
        setTimeout(() => {
          msg.removeClass('show');
          setTimeout(() => msg.remove(), 300);
        }, 2500);
      };

      // --- BOTÓN MENOS ---
      $minus.on('click', () => {
        let val = num() - step;

        // A) Si baja a 0 (o menos), eliminamos el ítem
        if (val <= 0) {
           // Buscamos el botón "X" (remove) en la misma fila y le hacemos click
           const removeBtn = $box.closest('tr').find('a.remove');
           if (removeBtn.length) {
             removeBtn[0].click(); 
             return; // Detenemos aquí para que Woo se encargue de borrar
           }
        }

        // B) Si es mayor a 0, respetamos el mínimo (si fuera > 1) y actualizamos
        if (!isNaN(min)) val = Math.max(min, val);
        $input.val(val).trigger('input').trigger('change');
      });

      // --- BOTÓN MÁS ---
      $plus.on('click', () => {
        let val = num() + step;
        
        // Verificar Stock Máximo
        if (!isNaN(max) && max > 0 && val > max) {
           showLimitMsg();
           return;
        }
        
        $input.val(val).trigger('input').trigger('change');
      });
    });
  }

  // Primera carga
  $(function () { enhanceQty($(document)); });

  // Reinyectar tras cada refresco de WooCommerce (AJAX)
  $(document.body).on('updated_wc_div wc_fragments_loaded wc_fragments_refreshed', function () {
    enhanceQty($('.woocommerce'));
  });
})(jQuery);
