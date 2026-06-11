// js/script.js
// Theme toggle (dark / light) with persistence
const toggleBtn = document.querySelector('.theme-toggle');
function setTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
}
function initTheme() {
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = saved || (prefersDark ? 'dark' : 'light');
  setTheme(theme);
  toggleBtn.textContent = theme === 'dark' ? '☀️' : '🌙';
}
toggleBtn.addEventListener('click', () => {
  const current = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  const newTheme = current === 'dark' ? 'light' : 'dark';
  setTheme(newTheme);
  toggleBtn.textContent = newTheme === 'dark' ? '☀️' : '🌙';
});
initTheme();

// Smooth scroll for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// Simple scroll‑reveal animation using IntersectionObserver
const revealElements = document.querySelectorAll('.card, .project-card');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });
revealElements.forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  observer.observe(el);
});
