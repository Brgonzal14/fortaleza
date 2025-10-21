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
    // Ancla el panel dentro del botón (queda justo debajo y alineado)
    if (cartPanel.parentElement !== cartBtn) cartBtn.appendChild(cartPanel);
    cartPanel.classList.add('cart-anchored');

    const openCart  = () => {
      // cierra catálogo si estuviera abierto
      closeIf(catPanel, catBtn);
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

    // Actualizar badge cuando Woo refresca el mini-cart vía AJAX
    if (window.jQuery) {
      jQuery(document.body).on('wc_fragments_refreshed', function(){
        try {
          const count = jQuery('.woocommerce-mini-cart li').length;
          const badge = document.getElementById('cart-count');
          if (badge) badge.textContent = count;
        } catch(_) {}
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
