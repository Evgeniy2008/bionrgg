// Utility functions
class Utils {
    static formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
    
    static copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                textArea.remove();
                return Promise.resolve();
            } catch (err) {
                textArea.remove();
                return Promise.reject(err);
            }
        }
    }
}

// Profiles page functionality
class ProfilesPage {
    constructor() {
        this.profiles = [];
        this.init();
    }

    init() {
        this.loadProfiles();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Refresh button if exists
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadProfiles();
            });
        }
    }

    async loadProfiles() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const profilesGrid = document.getElementById('profilesGrid');
        const profilesError = document.getElementById('profilesError');

        // Show loading state
        loadingSpinner.style.display = 'flex';
        profilesGrid.style.display = 'none';
        profilesError.style.display = 'none';

        try {
            const response = await fetch('api/leaderboard.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success && data.profiles) {
                this.profiles = data.profiles;
                this.renderProfiles();
                
                // Hide loading, show profiles
                loadingSpinner.style.display = 'none';
                profilesGrid.style.display = 'grid';
            } else {
                throw new Error(data.message || 'Failed to load profiles');
            }

        } catch (error) {
            console.error('Error loading profiles:', error);
            // Don't show error to user, just log it
            this.showEmptyState();
        }
    }

    renderProfiles() {
        const profilesGrid = document.getElementById('profilesGrid');
        
        if (this.profiles.length === 0) {
            profilesGrid.innerHTML = this.renderEmptyState();
            return;
        }

        const profilesHTML = this.profiles.map((profile, index) => {
            return this.renderProfileCard(profile, index + 1);
        }).join('');

        profilesGrid.innerHTML = profilesHTML;
        
        // Add click handlers for profile cards
        this.setupProfileCardHandlers();
    }

    renderProfileCard(profile, rank) {
        const rankClass = this.getRankClass(rank);
        const viewsFormatted = Utils.formatNumber(profile.views);
        const description = profile.descr || 'No description';
        const avatar = profile.avatar ? `data:image/jpeg;base64,${profile.avatar}` : 'assets/img/profile.png';
        
        return `
            <div class="profile-card" data-username="${profile.username}">
                <div class="profile-rank ${rankClass}">${rank}</div>
                
                <div class="profile-header">
                    <div class="profile-avatar">
                        ${profile.avatar ? 
                            `<img src="${avatar}" alt="${profile.username}">` : 
                            `<img src="assets/img/profile.png" alt="Default avatar" class="default-avatar">`
                        }
                    </div>
                    <div class="profile-info">
                        <h3>${profile.username}</h3>
                        <div class="profile-username">@${profile.username}</div>
                    </div>
                </div>

                <div class="profile-description">
                    ${description}
                </div>

                <div class="profile-stats">
                    <div class="profile-views">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="profile-views-count">${viewsFormatted}</span>
                        <span>views</span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="${profile.username}" class="profile-btn" target="_blank">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10,17 15,12 10,7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        View
                    </a>
                    <button class="profile-btn secondary" onclick="Utils.copyToClipboard('${window.location.origin}/${profile.username}')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Copy
                    </button>
                </div>
            </div>
        `;
    }

    renderEmptyState() {
        return `
            <div class="profiles-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <h3>No profiles yet</h3>
                <p>Be the first to create a profile on Bionrgg!</p>
                <a href="create.html" class="btn btn-primary">Create Profile</a>
            </div>
        `;
    }

    getRankClass(rank) {
        switch (rank) {
            case 1: return 'rank-1';
            case 2: return 'rank-2';
            case 3: return 'rank-3';
            default: return '';
        }
    }

    setupProfileCardHandlers() {
        const profileCards = document.querySelectorAll('.profile-card');
        
        profileCards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking on buttons
                if (e.target.closest('.profile-btn')) {
                    return;
                }
                
                const username = card.dataset.username;
                if (username) {
                    window.open(username, '_blank', 'noopener,noreferrer');
                }
            });

            // Add hover effects
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    }

    showError() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const profilesGrid = document.getElementById('profilesGrid');
        const profilesError = document.getElementById('profilesError');

        loadingSpinner.style.display = 'none';
        profilesGrid.style.display = 'none';
        profilesError.style.display = 'flex';
    }

    // Method to refresh profiles (can be called externally)
    refresh() {
        this.loadProfiles();
    }
}

// Initialize profiles page when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProfilesPage();
});

// Export for use in other scripts
window.ProfilesPage = ProfilesPage;
window.Utils = Utils;