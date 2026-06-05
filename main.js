// Contenido de main.js
const menuToggle = document.getElementById('menuToggle');
const headerNav = document.getElementById('headerNav');

menuToggle.addEventListener('click', () => {
  headerNav.classList.toggle('header__nav--open');
});