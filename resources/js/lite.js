// lite.js - Optimized JS for Sidebar and Dropdowns

// Sidebar Toggle
window.toggleSidebar = function () {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    sidebar.classList.toggle('collapsed');

    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Mobile Sidebar Toggle
window.toggleMobileSidebar = function () {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}

// Dropdown Toggle
window.toggleDropdown = function (id) {
    // Close all other dropdowns
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(dropdown => {
        if (dropdown.id !== id) {
            dropdown.classList.remove('show');
        }
    });

    const dropdown = document.getElementById(id);
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Load sidebar state
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }

    // Set active menu item
    const currentPath = window.location.pathname;
    document.querySelectorAll('.menu-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });

    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function (event) {
        const sidebar = document.querySelector('.sidebar');
        const mobileToggle = document.querySelector('.mobile-toggle');

        if (window.innerWidth <= 768 && sidebar) {
            if (!sidebar.contains(event.target) && !mobileToggle?.contains(event.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }

        // Close dropdowns when clicking outside
        if (!event.target.closest('.dropdown-container')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });

    // Keyboard shortcut for search
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === '/') {
            event.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    // Modals
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex'; // Ensure generic display before animation
            // Force reflow
            void modal.offsetWidth;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent body scroll
        }
    };

    window.closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300); // Match transition duration
        }
    };

    // Close modal on outside click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Initialize Theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
});

// Theme Toggle Logic
window.toggleTheme = function () {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
        // Optional: Change icon color for sun
        if (theme === 'light') {
            icon.style.color = '#f59e0b'; // Amber for sun
        } else {
            icon.style.color = ''; // Reset for moon
        }
    }
}
