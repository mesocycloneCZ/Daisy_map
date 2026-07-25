(() => {
  'use strict';

  const one = (selector) => document.querySelector(selector);
  const normalise = (value) => String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
    .toLowerCase();

  const onHomepage = () => {
    const path = location.pathname.replace(/index\.html$/i, '');
    return path === '/' || path === '';
  };

  function stableScroll(id, smooth = true) {
    const target = document.getElementById(id);
    if (!target) return;

    history.replaceState(null, '', `#${id}`);
    const times = [0, 90, 260, 650, 1300];
    times.forEach((delay, index) => {
      window.setTimeout(() => {
        const current = document.getElementById(id);
        if (!current) return;
        current.scrollIntoView({
          behavior: smooth && index === 0 ? 'smooth' : 'auto',
          block: 'start'
        });
      }, delay);
    });
  }

  function bindNavigation() {
    const contact = one('#kontakt');
    if (contact) {
      contact.hidden = false;
      contact.classList.remove('is-hidden');
      contact.style.order = '999';
    }

    document.querySelectorAll('#main-nav a, header nav a, nav a').forEach((link) => {
      const label = normalise(link.textContent);
      let target = '';

      if (label.includes('o projektu')) target = 'o-projektu';
      if (label.includes('kontakt')) target = 'kontakt';
      if (!target) return;

      link.setAttribute('href', `/#${target}`);
      if (link.dataset.mesocycloneV18 === '1') return;
      link.dataset.mesocycloneV18 = '1';

      link.addEventListener('click', (event) => {
        if (!onHomepage()) return;
        event.preventDefault();

        const menuToggle = one('#menu-toggle');
        if (menuToggle && menuToggle.getAttribute('aria-expanded') === 'true') {
          menuToggle.click();
        }
        stableScroll(target, true);
      });
    });
  }

  function setText(selector, value) {
    const element = one(selector);
    if (element && value !== undefined && value !== null && String(value).trim() !== '') {
      element.textContent = String(value);
    }
  }

  function setExternalLink(selector, url, label) {
    const element = one(selector);
    if (!element || !url) return;
    element.href = String(url);
    if (label) {
      const strong = element.querySelector('strong');
      if (strong) strong.textContent = label;
    }
  }

  async function loadContactData() {
    try {
      const response = await fetch('/api.php?route=%2Fapi%2Fsite', {
        cache: 'no-store',
        credentials: 'same-origin'
      });
      if (!response.ok) return;

      const json = await response.json();
      const config = json.site || (json.data && json.data.site) || json.data || json.config || json;
      const contact = config.contact || {};
      const links = config.links || {};

      setText('#contact-kicker', contact.kicker || 'KONTAKT');
      setText('#contact-title', contact.title || 'Spojte se s projektem Mesocyclone');
      setText('#contact-description', contact.description || contact.text);
      setText('#contact-email-label', contact.emailLabel || 'E-mail');
      setText('#contact-email', contact.email || links.contactEmail || 'info@mesocyclone.cz');

      setExternalLink('#contact-facebook', contact.facebookUrl || links.facebook || 'https://facebook.com/MesocycloneCZ', contact.facebookText || 'Mesocyclone ↗');
      setExternalLink('#contact-instagram', contact.instagramUrl || links.instagram || 'https://www.instagram.com/mesocyclonecz/', contact.instagramText || 'mesocyclonecz ↗');
      setExternalLink('#contact-youtube', contact.youtubeUrl || links.youtube || 'https://www.youtube.com/@MesoCyclone-h7m', contact.youtubeText || 'Mesocyclone ↗');

      const phone = contact.phone || links.phone || '';
      const phoneOption = one('#contact-phone-option');
      if (phoneOption) {
        phoneOption.hidden = !phone;
        if (phone) setText('#contact-phone', phone);
      }
    } catch (error) {
      console.warn('Kontaktní údaje nebylo možné načíst:', error);
    }
  }

  function handleInitialHash() {
    const id = location.hash.replace(/^#/, '');
    if (id === 'o-projektu' || id === 'kontakt') {
      stableScroll(id, false);
    }
  }

  function initialise() {
    bindNavigation();
    loadContactData();
    handleInitialHash();

    const observer = new MutationObserver(bindNavigation);
    observer.observe(document.documentElement, { childList: true, subtree: true });

    window.addEventListener('hashchange', handleInitialHash);
    window.addEventListener('load', handleInitialHash, { once: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialise, { once: true });
  } else {
    initialise();
  }
})();
