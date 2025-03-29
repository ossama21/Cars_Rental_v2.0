// Admin Dashboard Sidebar Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleSidebar = document.getElementById('toggleSidebar');
    const adminWrapper = document.getElementById('adminWrapper');
    const adminSidebar = document.getElementById('adminSidebar');
    
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
            if (window.innerWidth < 992) {
                adminSidebar.classList.toggle('show');
            }
        });
    }
    
    // Handle submenu toggles
    const submenuToggles = document.querySelectorAll('.has-submenu');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            
            const targetId = this.getAttribute('id');
            const submenu = document.getElementById(targetId.replace('Menu', 'Submenu'));
            
            if (submenu) {
                submenu.classList.toggle('active');
            }
        });
    });
    
    // Highlight active menu item based on current page
    function highlightActiveMenu() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.sidebar-menu-link, .submenu-item a');
        
        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href) && href !== '#') {
                
                link.classList.add('active');
                
                // If it's a submenu item, also activate parent
                if (link.closest('.submenu-item')) {
                    const submenu = link.closest('.sidebar-submenu');
                    if (submenu) {
                        submenu.classList.add('active');
                        
                        const parentId = submenu.getAttribute('id');
                        const parentLink = document.getElementById(parentId.replace('Submenu', 'Menu'));
                        if (parentLink) {
                            parentLink.classList.add('active');
                        }
                    }
                }
            }
        });
    }
    
    highlightActiveMenu();
});