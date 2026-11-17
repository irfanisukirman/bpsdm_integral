/* INTEGRAL appearance: apply before paint, persist locally, and keep controls accessible. */
(() => {
  'use strict';
  const key = 'integral:appearance';
  const root = document.documentElement;
  const system = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
  const valid = value => value === 'light' || value === 'dark';
  let stored = null;
  try { stored = localStorage.getItem(key); } catch (_) { /* Storage is optional. */ }
  let explicit = valid(stored);
  let current = explicit ? stored : (system?.matches ? 'dark' : 'light');

  function controls() {
    document.querySelectorAll('[data-integral-appearance]').forEach(button => {
      const active = button.dataset.integralAppearance === current;
      button.setAttribute('aria-pressed', String(active));
      button.classList.toggle('is-active', active);
    });
  }
  function charts() {
    const dark = current === 'dark';
    const text = dark ? '#e6eee8' : '#616161';
    const border = dark ? '#344f45' : '#e0e8e2';
    (window.Apex?._chartInstances || []).forEach(instance => {
      try { Promise.resolve(instance.chart.updateOptions({theme: {mode: current}, chart: {foreColor: text, background: 'transparent', fontFamily: 'Lato, sans-serif'}, grid: {borderColor: border}}, false, false)).catch(() => {}); } catch (_) { /* A chart may have been removed. */ }
    });
    if (window.Chart?.defaults?.font) window.Chart.defaults.font.family = 'Lato, sans-serif';
    if (window.Chart?.instances) Object.values(window.Chart.instances).forEach(chart => {
      if (!chart.options) return;
      chart.options.color = text;
      chart.options.font = {...(chart.options.font || {}), family: 'Lato, sans-serif'};
      if (chart.options.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = text;
      Object.values(chart.options.scales || {}).forEach(axis => {
        if (axis.ticks) axis.ticks.color = text;
        if (axis.grid) axis.grid.color = border;
      });
      try { chart.update('none'); } catch (_) { /* Unmounted charts are ignored. */ }
    });
  }
  function apply(theme, persist = false) {
    current = valid(theme) ? theme : 'light';
    root.dataset.integralTheme = current;
    root.dataset.bsTheme = current;
    if (persist) {
      explicit = true;
      try { localStorage.setItem(key, current); } catch (_) { /* Switching still works without storage. */ }
    }
    controls();
    if (document.readyState !== 'loading') charts();
    window.dispatchEvent(new CustomEvent('integral:themechange', {detail: {theme: current}}));
  }
  apply(current);
  window.IntegralTheme = {set: theme => apply(theme, true), get: () => current};
  document.addEventListener('DOMContentLoaded', () => {
    controls();
    document.querySelectorAll('[data-integral-appearance]').forEach(button => {
      button.addEventListener('click', () => apply(button.dataset.integralAppearance, true));
    });
    charts();
    document.fonts?.ready.then(charts);
  });
  window.addEventListener('storage', event => {
    if (event.key !== key) return;
    explicit = valid(event.newValue);
    apply(explicit ? event.newValue : (system?.matches ? 'dark' : 'light'));
  });
  system?.addEventListener?.('change', event => {
    if (!explicit) apply(event.matches ? 'dark' : 'light');
  });
})();
