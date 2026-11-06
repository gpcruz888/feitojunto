/* ============================================================
   feito. — Standalone & Modular Application Runtime (High-Performance)
   ============================================================ */

// Global Handlers
window.openProjectModal = function() {
  const formSection = document.getElementById('contato');
  if (formSection) {
    const y = formSection.getBoundingClientRect().top + window.scrollY - 90;
    window.scrollTo({ top: y, behavior: 'smooth' });
    const firstInput = document.getElementById('form-nome');
    if (firstInput) setTimeout(() => firstInput.focus(), 600);
  } else {
    window.location.href = '/#contato';
  }
};

window.closeProjectModal = function() {
  // Handled inline
};

document.addEventListener('DOMContentLoaded', () => {

  /* --- 0. Top Viewport Reading Progress Bar --- */
  let progressBar = document.getElementById('scroll-progress-bar');
  if (!progressBar) {
    progressBar = document.createElement('div');
    progressBar.id = 'scroll-progress-bar';
    progressBar.className = 'scroll-progress-bar';
    document.body.prepend(progressBar);
  }
  const updateScrollProgress = () => {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (totalHeight > 0) {
      const progress = (window.scrollY / totalHeight) * 100;
      progressBar.style.width = `${progress}%`;
    }
  };
  window.addEventListener('scroll', updateScrollProgress, { passive: true });
  updateScrollProgress();

  /* --- 0.1 Background Video Autoplay Guarantee --- */
  const heroVideo = document.querySelector('.hero__video-element');
  if (heroVideo) {
    heroVideo.muted = true;
    heroVideo.play().catch(() => {});
  }

  /* --- 1. Header scroll effect (blur & shadow elevation) --- */
  const header = document.getElementById('header');
  if (header) {
    const onScrollHeader = () => {
      header.classList.toggle('scrolled', window.scrollY > 30);
    };
    window.addEventListener('scroll', onScrollHeader, { passive: true });
    onScrollHeader();
  }

  /* --- 2. Mobile navigation menu --- */
  const toggle = document.getElementById('mobile-toggle');
  const menu = document.getElementById('mobile-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const isOpen = toggle.classList.toggle('active');
      menu.classList.toggle('active', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
    document.querySelectorAll('[data-mob]').forEach(link => {
      link.addEventListener('click', () => {
        toggle.classList.remove('active');
        menu.classList.remove('active');
        document.body.style.overflow = '';
      });
    });
  }

  /* --- 3. Scroll Reveal Observer & Animated Number Counters --- */
  const revealElements = document.querySelectorAll('.reveal, .reveal-scale, .gestcorp-enter');
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');

          if (entry.target.classList.contains('hero__stats') || entry.target.classList.contains('hero__stats-card')) {
            triggerStatsCounter();
          }

          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.05, rootMargin: '0px 0px -40px 0px' }
  );
  revealElements.forEach(el => revealObserver.observe(el));

  let statsAnimated = false;
  function triggerStatsCounter() {
    if (statsAnimated) return;
    statsAnimated = true;

    const statNumbers = document.querySelectorAll('.stat-card__number, .stat-item__number');
    statNumbers.forEach(stat => {
      const target = parseInt(stat.getAttribute('data-target'), 10);
      const prefix = stat.getAttribute('data-prefix') || '';
      const suffix = stat.getAttribute('data-suffix') || '';
      if (isNaN(target)) return;

      const duration = 1600; // ms
      const startTime = performance.now();

      function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const currentVal = Math.floor(easeOut * target);

        stat.textContent = `${prefix}${currentVal}${suffix}`;

        if (progress < 1) {
          requestAnimationFrame(updateNumber);
        } else {
          stat.textContent = `${prefix}${target}${suffix}`;
        }
      }

      requestAnimationFrame(updateNumber);
    });
  }

  const statsBar = document.querySelector('.hero__stats-card, .hero__stats');
  if (statsBar) {
    const rect = statsBar.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom > 0) {
      setTimeout(() => {
        triggerStatsCounter();
      }, 250);
    }
  }

  /* --- 3.5 Interactive Cursor Spotlight on Cards --- */
  const spotlightCards = document.querySelectorAll('.svc-card, .proj-card');
  spotlightCards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--mouse-x', `${x}px`);
      card.style.setProperty('--mouse-y', `${y}px`);
    }, { passive: true });
  });

  /* --- 3.6 Magnetic Micro-Pull on Buttons --- */
  if (window.innerWidth >= 1024) {
    const magneticBtns = document.querySelectorAll('.header__cta, .whatsapp-fab, .projects__more');
    magneticBtns.forEach(btn => {
      btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const deltaX = (e.clientX - centerX) * 0.2;
        const deltaY = (e.clientY - centerY) * 0.2;
        btn.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
      }, { passive: true });

      btn.addEventListener('mouseleave', () => {
        btn.style.transform = '';
      });
    });
  }

  /* --- 4. Process Connected Timeline Scroll Progress --- */
  const processSection = document.getElementById('processo');
  const processFill = document.getElementById('process-progress');
  const processSteps = document.querySelectorAll('.process-step');

  const updateProcessTimeline = () => {
    if (!processSection || !processFill || window.innerWidth < 1024) return;
    const rect = processSection.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    
    if (rect.top < windowHeight * 0.7 && rect.bottom > 0) {
      const progress = Math.min(Math.max((windowHeight * 0.7 - rect.top) / (rect.height * 0.8), 0), 1);
      processFill.style.width = `${progress * 100}%`;

      processSteps.forEach((step, idx) => {
        const num = step.querySelector('.process-step__num');
        if (num) {
          if (progress >= (idx / processSteps.length) * 0.8) {
            num.style.borderColor = 'var(--text-dark)';
            num.style.color = 'var(--text-dark)';
            num.style.boxShadow = '0 6px 18px rgba(0, 0, 0, 0.12)';
          } else {
            num.style.borderColor = 'rgba(0, 0, 0, 0.12)';
            num.style.color = 'rgba(0, 0, 0, 0.4)';
            num.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.04)';
          }
        }
      });
    }
  };
  window.addEventListener('scroll', updateProcessTimeline, { passive: true });

  /* --- 5. Interactive 3D Mouse Movement on Hero Notebook (Lightweight GPU) --- */
  const heroSection = document.getElementById('hero');
  const mockupWrapper = document.querySelector('.hero__mockup-wrapper');
  const notebook = document.querySelector('.hero__notebook');

  if (notebook && mockupWrapper && heroSection && window.innerWidth >= 1024) {
    let ticking = false;

    heroSection.addEventListener('mousemove', (e) => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          const rect = mockupWrapper.getBoundingClientRect();
          const centerX = rect.left + rect.width / 2;
          const centerY = rect.top + rect.height / 2;

          const normX = (e.clientX - centerX) / (window.innerWidth / 2);
          const normY = (e.clientY - centerY) / (window.innerHeight / 2);

          const rotateY = Math.max(Math.min(normX * 14, 18), -18);
          const rotateX = Math.max(Math.min(-normY * 12, 14), -14);

          notebook.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
          notebook.style.filter = `drop-shadow(${-rotateY * 2}px ${30 + rotateX * 2}px 60px rgba(0, 0, 0, 0.95))`;
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    heroSection.addEventListener('mouseleave', () => {
      notebook.style.transform = `perspective(1200px) rotateX(0deg) rotateY(0deg) scale(1)`;
      notebook.style.filter = `drop-shadow(0 32px 64px rgba(0, 0, 0, 0.95))`;
    });
  }

  /* --- 6. Smooth Anchor Links --- */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const id = a.getAttribute('href');
      if (id === '#' || !id || id === '#!') return;
      const target = document.querySelector(id);
      if (target) {
        e.preventDefault();
        const y = target.getBoundingClientRect().top + window.scrollY - 90;
        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    });
  });

  /* --- 7. Active Nav Link Highlighting --- */
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.header__link');
  const highlightNav = () => {
    const pos = window.scrollY + 240;
    sections.forEach(sec => {
      const top = sec.offsetTop;
      const h = sec.offsetHeight;
      const id = sec.getAttribute('id');
      if (pos >= top && pos < top + h) {
        navLinks.forEach(l => {
          const isActive = l.getAttribute('href') === `#${id}`;
          l.style.color = isActive ? 'var(--text-white)' : '';
          l.style.background = isActive ? 'rgba(255, 255, 255, 0.1)' : '';
        });
      }
    });
  };
  window.addEventListener('scroll', highlightNav, { passive: true });

  /* --- 8. Testimonial Interactive Quotes --- */
  const testimonials = [
    {
      quote: "O trabalho do feito. foi essencial para a evolução da nossa presença digital. Organização, agilidade e um resultado acima do esperado.",
      name: "Bruno Gabriel",
      role: "Santa Maria Imobiliária",
      initials: "BG"
    },
    {
      quote: "A clareza no processo e a atenção meticulosa aos detalhes nos permitiram lançar nossa nova plataforma com velocidade e alto nível técnico.",
      name: "Camila Duarte",
      role: "Discover Tech",
      initials: "CD"
    }
  ];
  let currentTestimonial = 0;
  const quoteEl = document.querySelector('.testimonial__text');
  const nameEl = document.querySelector('.testimonial__name');
  const roleEl = document.querySelector('.testimonial__role');
  const avatarEl = document.querySelector('.testimonial__avatar-pill');
  const prevTestBtn = document.getElementById('test-prev');
  const nextTestBtn = document.getElementById('test-next');

  const updateTestimonial = (index) => {
    if (!quoteEl || !nameEl || !roleEl) return;
    quoteEl.style.opacity = '0';
    quoteEl.style.transform = 'translateY(8px)';
    setTimeout(() => {
      const t = testimonials[index];
      quoteEl.textContent = t.quote;
      nameEl.textContent = t.name;
      roleEl.textContent = t.role;
      if (avatarEl) avatarEl.textContent = t.initials;
      quoteEl.style.transition = 'opacity 0.4s var(--ease), transform 0.4s var(--ease)';
      quoteEl.style.opacity = '1';
      quoteEl.style.transform = 'translateY(0)';
    }, 200);
  };

  if (prevTestBtn && nextTestBtn) {
    prevTestBtn.addEventListener('click', () => {
      currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
      updateTestimonial(currentTestimonial);
    });
    nextTestBtn.addEventListener('click', () => {
      currentTestimonial = (currentTestimonial + 1) % testimonials.length;
      updateTestimonial(currentTestimonial);
    });
  }

  /* --- 9. Modal Event Delegation & Form Submission --- */
  const modal = document.getElementById('project-modal');
  const form = document.getElementById('project-briefing-form');
  const formWrapper = document.getElementById('modal-form-wrapper');
  const successScreen = document.getElementById('modal-success-screen');
  const resetBtn = document.getElementById('modal-reset-btn');

  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-open-modal="project-modal"], .header__cta, [data-open-modal]');
    if (trigger) {
      e.preventDefault();
      window.openProjectModal();
      return;
    }
  });

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      const formData = new FormData(form);
      const dataObj = Object.fromEntries(formData.entries());

      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 0.8s linear infinite; margin-right: 8px;">
          <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
          <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.95"/>
        </svg>
        enviando proposta...
      `;

      try {
        const payload = {
          nome: dataObj.nome || 'Não informado',
          email: dataObj.email || 'Não informado',
          whatsapp: dataObj.whatsapp || 'Não informado',
          tipo_projeto: dataObj.tipo_projeto || 'Geral',
          prazo_orcamento: dataObj.orcamento || 'Não especificado',
          mensagem: dataObj.mensagem || 'Sem mensagem adicional',
          _hp: dataObj._hp || '',
          _subject: `Novo Projeto Solicitado — ${dataObj.nome} (${dataObj.tipo_projeto || 'Web'})`,
        };

        // Tenta envio direto pelo backend PHP local se disponível
        try {
          await fetch('/api/send-mail.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
          });
        } catch (phpErr) {
          // Fallback para FormSubmit API
          await fetch('https://formsubmit.co/ajax/gabriel@feitojunto.com', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
          });
        }

        if (formWrapper) formWrapper.style.display = 'none';
        if (successScreen) successScreen.classList.add('is-visible');

        const waSuccessBtn = document.getElementById('modal-wa-success');
        if (waSuccessBtn) {
          const waText = encodeURIComponent(
            `Olá, Gabriel! Acabei de enviar meu formulário de projeto pelo site da feito.\n\n*Nome:* ${dataObj.nome}\n*Projeto:* ${dataObj.tipo_projeto || 'Web'}\n*WhatsApp:* ${dataObj.whatsapp}`
          );
          waSuccessBtn.href = `https://api.whatsapp.com/send/?phone=5511946898620&text=${waText}&type=phone_number&app_absent=0`;
        }

      } catch (err) {
        console.warn('Envio via API falhou, acionando fallback:', err);
        if (formWrapper) formWrapper.style.display = 'none';
        if (successScreen) successScreen.classList.add('is-visible');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      form.reset();
      if (successScreen) successScreen.classList.remove('is-visible');
      if (formWrapper) formWrapper.style.display = 'block';
    });
  }

});
