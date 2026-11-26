/* Manual de usuario COLSOFTCO - comportamiento extraido del manual original */
(function () {
  'use strict';

  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const navOverlay = document.getElementById('navOverlay');
  const menuToggle = document.getElementById('menuToggle');
  const sidebarClose = document.getElementById('sidebarClose');
  const navSearch = document.getElementById('navSearch');
  const navEmpty = document.getElementById('navEmpty');
  const navLinks = Array.from(document.querySelectorAll('.nav-link'));
  const navGroups = Array.from(document.querySelectorAll('.nav-group'));
  const sections = Array.from(document.querySelectorAll('main section[id]'));
  const progressBar = document.getElementById('progressBar');
  const currentSection = document.getElementById('currentSection');
  const toTop = document.getElementById('toTop');
  const lightbox = document.getElementById('lightbox');
  const lightboxImage = document.getElementById('lightboxImage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxClose = document.getElementById('lightboxClose');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const navMap = new Map();

  navLinks.forEach(function (link) {
    navMap.set(link.getAttribute('href'), link);
  });

  function setNav(open) {
    body.classList.toggle('nav-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
  }

  menuToggle.addEventListener('click', function () {
    setNav(!body.classList.contains('nav-open'));
  });

  navOverlay.addEventListener('click', function () {
    setNav(false);
  });

  sidebarClose.addEventListener('click', function () {
    setNav(false);
    menuToggle.focus();
  });

  navLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.matchMedia('(max-width: 1020px)').matches) {
        setNav(false);
      }
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && body.classList.contains('nav-open')) {
      setNav(false);
    }
  });

  navSearch.addEventListener('input', function () {
    const query = navSearch.value.trim().toLowerCase();
    let visibleCount = 0;

    navLinks.forEach(function (link) {
      const matches = !query || link.textContent.toLowerCase().includes(query);
      link.parentElement.hidden = !matches;
      if (matches) {
        visibleCount += 1;
      }
    });

    navGroups.forEach(function (group) {
      const hasVisible = Array.from(group.querySelectorAll('li')).some(function (item) {
        return !item.hidden;
      });
      group.hidden = !hasVisible;
    });

    navEmpty.hidden = visibleCount > 0;
  });

  navSearch.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      const firstVisible = navLinks.find(function (link) {
        return !link.parentElement.hidden;
      });
      if (firstVisible) {
        firstVisible.click();
        firstVisible.focus();
      }
    }
  });

  const revealItems = Array.from(document.querySelectorAll('.reveal'));

  if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -8% 0px' });

    revealItems.forEach(function (item) {
      revealObserver.observe(item);
    });
  } else {
    revealItems.forEach(function (item) {
      item.classList.add('is-visible');
    });
  }

  let ticking = false;

  function updateScrollUI() {
    const doc = document.documentElement;
    const maxScroll = doc.scrollHeight - window.innerHeight;
    const scrollTop = window.scrollY || doc.scrollTop;
    const progress = maxScroll > 0 ? (scrollTop / maxScroll) * 100 : 0;
    progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
    toTop.classList.toggle('is-visible', scrollTop > 620);

    let activeSection = sections[0];

    sections.forEach(function (section) {
      if (section.getBoundingClientRect().top <= 140) {
        activeSection = section;
      }
    });

    if (scrollTop + window.innerHeight >= doc.scrollHeight - 4) {
      activeSection = sections[sections.length - 1];
    }

    if (activeSection) {
      const hash = '#' + activeSection.id;
      const activeLink = navMap.get(hash);
      const title = activeSection.getAttribute('data-title') || (activeLink ? activeLink.textContent.trim() : 'Inicio');

      currentSection.textContent = title;

      navLinks.forEach(function (link) {
        const isActive = link === activeLink;
        link.classList.toggle('is-active', isActive);
        if (isActive) {
          link.setAttribute('aria-current', 'location');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    }

    ticking = false;
  }

  function requestScrollUpdate() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(updateScrollUI);
    }
  }

  window.addEventListener('scroll', requestScrollUpdate, { passive: true });
  window.addEventListener('resize', requestScrollUpdate);
  updateScrollUI();

  toTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
  });

  document.querySelectorAll('.shot').forEach(function (button) {
    button.addEventListener('click', function () {
      const image = button.querySelector('img');
      lightboxImage.src = image.currentSrc || image.src;
      lightboxImage.alt = image.alt;
      lightboxCaption.textContent = button.getAttribute('data-caption') || image.alt;
      lightbox.showModal();
      body.classList.add('dialog-open');
    });
  });

  lightboxClose.addEventListener('click', function () {
    lightbox.close();
  });

  lightbox.addEventListener('click', function (event) {
    const rect = lightbox.getBoundingClientRect();
    const outside = event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom;
    if (outside) {
      lightbox.close();
    }
  });

  lightbox.addEventListener('close', function () {
    body.classList.remove('dialog-open');
    lightboxImage.src = '';
  });

  document.querySelectorAll('[data-print]').forEach(function (button) {
    button.addEventListener('click', function () {
      window.print();
    });
  });

  let printState = [];

  window.addEventListener('beforeprint', function () {
    printState = Array.from(document.querySelectorAll('details')).map(function (item) {
      return item.open;
    });
    document.querySelectorAll('details').forEach(function (item) {
      item.open = true;
    });
  });

  window.addEventListener('afterprint', function () {
    document.querySelectorAll('details').forEach(function (item, index) {
      item.open = Boolean(printState[index]);
    });
  });
})();
