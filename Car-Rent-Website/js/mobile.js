/**
 * Mobile navigation handling for CARSRENT
 */
document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.querySelector('.menu-toggle');
  const navMenu = document.querySelector('.nav-menu');
  const mobileNav = document.querySelector('.mobile-nav');
  const body = document.body;
  
  if (menuToggle && mobileNav) {
    // Toggle mobile menu
    menuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      menuToggle.classList.toggle('active');
      mobileNav.classList.toggle('active');
      body.classList.toggle('menu-open');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!menuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
        menuToggle.classList.remove('active');
        mobileNav.classList.remove('active');
        body.classList.remove('menu-open');
      }
    });
    
    // Add touch support for mobile devices
    if ('ontouchstart' in window) {
      menuToggle.addEventListener('touchstart', function(e) {
        e.stopPropagation();
      });
    }
  }
  
  // Sync active links between desktop and mobile navigation
  const syncActiveLinks = () => {
    const desktopLinks = document.querySelectorAll('.nav-link');
    const mobileLinks = document.querySelectorAll('.mobile-nav-link');
    
    // Get current page path
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    
    // Set active classes
    desktopLinks.forEach(link => {
      const linkHref = link.getAttribute('href');
      if (linkHref === currentPath || 
          (currentPath === 'index.php' && linkHref === '#home')) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
    
    mobileLinks.forEach(link => {
      const linkHref = link.getAttribute('href');
      if (linkHref === currentPath || 
          (currentPath === 'index.php' && linkHref === '#home')) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  };
  
  // Run on page load
  syncActiveLinks();
});