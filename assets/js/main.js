// Main JavaScript functionality
class BionrggApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAuthButtons();
    }

    setupEventListeners() {
        // Mobile navigation toggle
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.querySelector('.nav-links');
        
        console.log('Setting up navigation toggle...');
        console.log('navToggle found:', !!navToggle);
        console.log('navLinks found:', !!navLinks);
        
        if (navToggle && navLinks) {
            console.log('Adding click listener to navToggle');
            navToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                
                const isActive = navLinks.classList.contains('active');
                
                if (isActive) {
                    // Close menu
                    navLinks.classList.remove('active');
                    navToggle.classList.remove('active');
                    document.body.classList.remove('menu-open');
                    document.body.style.overflow = '';
                } else {
                    // Open menu
                    navLinks.classList.add('active');
                    navToggle.classList.add('active');
                    document.body.classList.add('menu-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        } else {
            console.error('Nav toggle or navLinks not found!');
        }

        // Close mobile menu when clicking outside or on links
        document.addEventListener('click', (e) => {
            if (navLinks && navLinks.classList.contains('active')) {
                // Close if clicking outside menu and toggle button
                if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
                    this.closeMobileMenu();
                }
            }
        }, true);
        
        // Close menu when clicking on nav links
        if (navLinks) {
            navLinks.addEventListener('click', (e) => {
                if (e.target.classList.contains('nav-link')) {
                    this.closeMobileMenu();
                }
            });
        }
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navLinks && navLinks.classList.contains('active')) {
                this.closeMobileMenu();
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupAuthButtons() {
        this.updateAuthButtons();
        
        // Listen for auth state changes
        window.addEventListener('storage', (e) => {
            if (e.key === 'userID' || e.key === 'password') {
                console.log('Auth state changed:', e.key);
                this.updateAuthButtons();
            }
        });
        
        // Also check on page load
        setTimeout(() => {
            this.updateAuthButtons();
        }, 100);
    }

    updateAuthButtons() {
        const isLoggedIn = this.isUserLoggedIn();
        const navLinks = document.querySelector('.nav-links');
        
        console.log('Updating auth buttons, isLoggedIn:', isLoggedIn);
        
        if (!navLinks) {
            console.log('No nav-links found');
            return;
        }

        // Find existing auth buttons
        const loginLink = navLinks.querySelector('a[href="login.html"]');
        const createLink = navLinks.querySelector('a[href="create.html"]');
        const myProfileLink = navLinks.querySelector('a[href="my-profile.html"]');
        
        if (isLoggedIn) {
            // Hide login and create buttons
            if (loginLink) loginLink.style.display = 'none';
            if (createLink) createLink.style.display = 'none';
            
            // Show my profile and logout buttons
            if (myProfileLink) {
                myProfileLink.style.display = 'inline-block';
            }
            
            // Add logout button if it doesn't exist
            if (!navLinks.querySelector('.logout-btn')) {
                const logoutBtn = document.createElement('button');
                logoutBtn.className = 'btn btn-outline logout-btn';
                logoutBtn.innerHTML = `
                    <span>Logout</span>
                `;
                logoutBtn.addEventListener('click', () => this.logout());
                navLinks.appendChild(logoutBtn);
            }
        } else {
            // Show login and create buttons
            if (loginLink) loginLink.style.display = 'inline-block';
            if (createLink) createLink.style.display = 'inline-block';
            
            // Hide my profile button
            if (myProfileLink) {
                myProfileLink.style.display = 'none';
            }
            
            // Remove logout button
            const logoutBtn = navLinks.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.remove();
            }
        }
    }

    isUserLoggedIn() {
        const username = localStorage.getItem('userID');
        const password = localStorage.getItem('password');
        return !!(username && password);
    }
    
    closeMobileMenu() {
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (navLinks && navToggle) {
            navLinks.classList.remove('active');
            navToggle.classList.remove('active');
            document.body.classList.remove('menu-open');
            document.body.style.overflow = '';
        }
    }

    logout() {
        localStorage.removeItem('userID');
        localStorage.removeItem('password');
        localStorage.removeItem('userProfile');
        
        // Clear cookies
        document.cookie = 'userProfile=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        
        // Update buttons
        this.updateAuthButtons();
        
        // Show success message
        if (window.Utils && window.Utils.showNotification) {
            window.Utils.showNotification('Logout successful!', 'success');
        }
        
        // Redirect to home page
        if (window.location.pathname.includes('my-profile.html')) {
            window.location.href = 'index.html';
        }
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BionrggApp();
});

// Export for use in other scripts
window.BionrggApp = BionrggApp;