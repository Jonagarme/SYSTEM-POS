/**
 * Global UI Logic (Sidebar, Submenus)
 */
document.addEventListener('DOMContentLoaded', () => {
    // Toggle Sidebar
    const toggleBtn = document.getElementById('toggle-sidebar');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                document.body.classList.toggle('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });
    }

    // Close sidebar on click outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && 
            document.body.classList.contains('sidebar-open') && 
            !e.target.closest('.sidebar') && 
            !e.target.closest('#toggle-sidebar')) {
            document.body.classList.remove('sidebar-open');
        }
    });

    // Submenu toggle logic
    document.querySelectorAll('.has-submenu > a').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('open');
        });
    });

    // Global Search Logic (Sidebar Filter)
    const globalSearchInput = document.querySelector('.search-bar input');
    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const sidebarItems = document.querySelectorAll('.sidebar-nav > ul > li');

            sidebarItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                const hasMatch = text.includes(query);
                
                // Show/Hide top-level items
                item.style.display = hasMatch ? '' : 'none';

                // If it's a submenu parent, handle its children and open state
                if (item.classList.contains('has-submenu')) {
                    const subItems = item.querySelectorAll('.submenu li');
                    let subMatch = false;

                    subItems.forEach(sub => {
                        const subText = sub.textContent.toLowerCase();
                        const subMatches = subText.includes(query);
                        sub.style.display = subMatches ? '' : 'none';
                        if (subMatches) subMatch = true;
                    });

                    // If any subitem matches, show the parent and open it
                    if (subMatch) {
                        item.style.display = '';
                        item.classList.add('open');
                    } else if (!hasMatch) {
                        item.style.display = 'none';
                        item.classList.remove('open');
                    }
                }
            });

            // Reset when empty
            if (query === '') {
                document.querySelectorAll('.has-submenu').forEach(item => {
                    if (!item.classList.contains('active')) {
                        item.classList.remove('open');
                    }
                });
            }
        });
    }
});

/**
 * Show Premium Toast Notification
 * @param {string} title 
 * @param {string} message 
 * @param {string} type (success, error, warning, info)
 */
function showToast(title, message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close">
            <i class="fas fa-times"></i>
        </div>
    `;

    container.appendChild(toast);

    // Show with a tiny delay for animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Auto remove
    const timer = setTimeout(() => {
        closeToast(toast);
    }, 5000);

    // Manual close
    toast.querySelector('.toast-close').addEventListener('click', () => {
        clearTimeout(timer);
        closeToast(toast);
    });
}

function closeToast(toast) {
    toast.classList.remove('show');
    setTimeout(() => {
        toast.remove();
    }, 400);
}

