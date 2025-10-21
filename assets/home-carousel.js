// --- Carrusel (adaptado a WP) ---
(function(){
  const slides   = Array.from(document.querySelectorAll('.fort-carousel .slide'));
  if (!slides.length) return;

  const dotsWrap = document.querySelector('.fort-carousel #dots');
  const prev     = document.querySelector('.fort-carousel #prevBtn');
  const next     = document.querySelector('.fort-carousel #nextBtn');
  const carousel = document.querySelector('.fort-carousel');

  let i = 0, timer = null;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const getDelay = (idx) => (idx === 0 ? 8000 : 5000);

  function renderDots(){
    dotsWrap.innerHTML = '';
    slides.forEach((_, idx)=>{
      const b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', 'Ir al slide ' + (idx+1));
      if (idx === i) b.setAttribute('aria-current', 'true');
      b.addEventListener('click', ()=>go(idx, true));
      dotsWrap.appendChild(b);
    });
  }
  function updateDots(){
    dotsWrap.querySelectorAll('button').forEach((d, idx)=>{
      if (idx === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
    });
  }
  function go(n, user){
    slides[i].classList.remove('is-active');
    slides[i].setAttribute('aria-hidden','true');
    i = (n + slides.length) % slides.length;
    slides[i].classList.add('is-active');
    slides[i].setAttribute('aria-hidden','false');
    updateDots();
    if (user) restart();
  }
  function start(){ if (!timer && !reduceMotion) timer = setTimeout(tick, getDelay(i)); }
  function tick(){ go(i+1); timer = setTimeout(tick, getDelay(i)); }
  function stop(){ if (timer){ clearTimeout(timer); timer=null; } }
  function restart(){ stop(); start(); }

  next.addEventListener('click', ()=>go(i+1, true));
  prev.addEventListener('click', ()=>go(i-1, true));

  // pausa en hover
  carousel.addEventListener('mouseenter', stop);
  carousel.addEventListener('mouseleave', start);

  // teclado (cuando el carrusel tiene foco)
  carousel.setAttribute('tabindex','0');
  carousel.addEventListener('keydown', (e)=>{
    if (e.key==='ArrowRight') { e.preventDefault(); go(i+1, true); }
    if (e.key==='ArrowLeft')  { e.preventDefault(); go(i-1, true); }
  });

  // swipe
  let x0=null;
  carousel.addEventListener('touchstart', e=>{ x0=e.touches[0].clientX }, {passive:true});
  carousel.addEventListener('touchend', e=>{
    if (x0==null) return;
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx)>40) go(i + (dx<0?1:-1), true);
    x0=null;
  });

  renderDots();
  slides.forEach((s, idx)=>{ s.classList.toggle('is-active', idx===0); s.setAttribute('aria-hidden', idx? 'true':'false'); });
  start();
})();
