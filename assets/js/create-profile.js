// Create Profile Preview functionality
class CreateProfilePreview {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updatePreview();
    }

    setupEventListeners() {
        // Profile type selector
        const profileTypeRadios = document.querySelectorAll('input[name="profileType"]');
        const companyNameGroup = document.getElementById('companyNameGroup');
        const companyNameInput = document.getElementById('companyName');
        const companyKeyGroup = document.getElementById('companyKeyGroup');
        const companyKeyInput = document.getElementById('companyKey');
        
        // Initialize on page load - show company key field for personal profile
        const personalRadio = document.querySelector('input[name="profileType"][value="personal"]');
        if (personalRadio && personalRadio.checked) {
            if (companyNameGroup) companyNameGroup.style.display = 'none';
            if (companyKeyGroup) companyKeyGroup.style.display = 'block';
        }
        
        profileTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'company') {
                    // Creating new company - show company name field
                    if (companyNameGroup) companyNameGroup.style.display = 'block';
                    if (companyKeyGroup) companyKeyGroup.style.display = 'none';
                    if (companyNameInput) {
                        companyNameInput.setAttribute('required', 'required');
                    }
                    if (companyKeyInput) {
                        companyKeyInput.removeAttribute('required');
                        companyKeyInput.value = '';
                    }
                } else {
                    // Personal profile - show company key field for joining
                    if (companyNameGroup) companyNameGroup.style.display = 'none';
                    if (companyKeyGroup) companyKeyGroup.style.display = 'block';
                    if (companyNameInput) {
                        companyNameInput.removeAttribute('required');
                        companyNameInput.value = '';
                    }
                    if (companyKeyInput) {
                        companyKeyInput.removeAttribute('required');
                    }
                }
            });
        });
        
        // Auto-uppercase company key input
        if (companyKeyInput) {
            companyKeyInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase();
            });
        }

        // Form inputs for live preview
        const textInputs = ['username', 'description'];
        textInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        // Color inputs for live preview
        const colorInputs = ['profileColor', 'textColor'];
        colorInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        // Avatar preview
        const avatarInput = document.getElementById('avatar');
        if (avatarInput) {
            avatarInput.addEventListener('change', (e) => this.handleAvatarPreview(e));
        }

        // Social media inputs for live preview
        const socialInputs = [
            'instagram', 'discord', 'facebook', 'steam', 'twitch', 'tiktok', 'telegram', 'youtube', 'youtubeMusic', 'viber',
            'googleDocs', 'googleSheets', 'fileUpload',
            'dou', 'olx', 'amazon', 'prom', 'fhunt', 'dj',
            'privatBank', 'monoBank', 'alfaBank', 'abank', 'pumbBank', 'raiffeisenBank', 'senseBank',
            'binance', 'trustWallet'
        ];
        socialInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });
    }

    // Function to check if color is dark
    isDarkColor(color) {
        // Convert hex to RGB
        const hex = color.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        // Calculate luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        
        // If luminance is less than 0.5, color is dark
        return luminance < 0.5;
    }


    handleAvatarPreview(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewImg = document.getElementById('previewAvatarImg');
                if (previewImg) {
                    previewImg.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    }

    updatePreview() {
        // Update name
        const usernameInput = document.getElementById('username');
        const previewName = document.getElementById('previewName');
        if (usernameInput && previewName) {
            const username = usernameInput.value.trim() || 'Ваше ім\'я';
            previewName.textContent = username;
        }

        // Update description
        const descriptionInput = document.getElementById('description');
        const previewDescription = document.getElementById('previewDescription');
        if (descriptionInput && previewDescription) {
            const description = descriptionInput.value.trim() || 'Ваш опис профілю';
            previewDescription.textContent = description;
        }

        // Update colors
        const profileColor = document.getElementById('profileColor')?.value || '#2572ad';
        const textColor = document.getElementById('textColor')?.value || '#ffffff';
        const previewCard = document.getElementById('profilePreviewCard');
        
        if (previewCard) {
            // Apply colors directly - use !important via setProperty to ensure they override
            previewCard.style.setProperty('background-color', profileColor, 'important');
            previewCard.style.setProperty('color', textColor, 'important');
            
            // Also update text elements inside to ensure they use the text color
            const previewName = document.getElementById('previewName');
            const previewDescription = document.getElementById('previewDescription');
            const previewLinks = document.getElementById('previewLinks');
            
            if (previewName) {
                previewName.style.setProperty('color', textColor, 'important');
            }
            if (previewDescription) {
                previewDescription.style.setProperty('color', textColor, 'important');
            }
            
            // Update all links inside preview to use text color
            if (previewLinks) {
                const links = previewLinks.querySelectorAll('.profile-preview-link');
                links.forEach(link => {
                    link.style.setProperty('color', textColor, 'important');
                });
            }
        }

        // Update links
        this.updatePreviewLinks();
    }

    updatePreviewLinks() {
        const previewLinks = document.getElementById('previewLinks');
        if (!previewLinks) return;

        // Get current text color to apply to links
        const textColor = document.getElementById('textColor')?.value || '#ffffff';

        const socialInputs = [
            { id: 'instagram', name: 'Instagram', icon: 'insta.png' },
            { id: 'discord', name: 'Discord', icon: 'discord.png' },
            { id: 'facebook', name: 'Facebook', icon: 'facebook.png' },
            { id: 'steam', name: 'Steam', icon: 'steam.png' },
            { id: 'twitch', name: 'Twitch', icon: 'twitch.png' },
            { id: 'tiktok', name: 'TikTok', icon: 'tiktok.png' },
            { id: 'telegram', name: 'Telegram', icon: 'tg.png' },
            { id: 'youtube', name: 'YouTube', icon: 'youtube.png' },
            { id: 'youtubeMusic', name: 'YouTube Music', icon: 'youtubeMusic.png' },
            { id: 'viber', name: 'Viber', icon: 'viber.png' },
            { id: 'googleDocs', name: 'Google Docs', icon: 'google docs.png' },
            { id: 'googleSheets', name: 'Google Sheets', icon: 'googlesheets.png' },
            { id: 'fileUpload', name: 'File', icon: 'file.png' },
            { id: 'dou', name: 'DOU', icon: 'dou.png' },
            { id: 'olx', name: 'OLX', icon: 'olx.png' },
            { id: 'prom', name: 'Prom.ua', icon: 'prom.png' },
            { id: 'amazon', name: 'Amazon', icon: 'amazon.png' },
            { id: 'fhunt', name: 'FHunt', icon: 'fhunt.png' },
            { id: 'dj', name: 'DJ', icon: 'dj.png' },
            { id: 'privatBank', name: 'ПриватБанк', icon: 'privatBank.png' },
            { id: 'monoBank', name: 'Монобанк', icon: 'monoBank.png' },
            { id: 'alfaBank', name: 'Альфа-Банк', icon: 'alfaBank.png' },
            { id: 'abank', name: 'А-Банк', icon: 'abank.png' },
            { id: 'pumbBank', name: 'ПУМБ', icon: 'pumbBank.png' },
            { id: 'raiffeisenBank', name: 'Райффайзен Банк', icon: 'raiffeisenBank.png' },
            { id: 'senseBank', name: 'Sense Bank', icon: 'senseBank.png' },
            { id: 'binance', name: 'Binance', icon: 'binance.png' },
            { id: 'trustWallet', name: 'Trust Wallet', icon: 'trustWallet.png' }
        ];

        const links = socialInputs.filter(social => {
            const input = document.getElementById(social.id);
            return input && input.value.trim() !== '';
        });

        if (links.length === 0) {
            previewLinks.innerHTML = `<p style="text-align: center; color: ${textColor}; opacity: 0.7;">Додайте посилання на соціальні мережі</p>`;
            return;
        }

        previewLinks.innerHTML = links.map(social => {
            const input = document.getElementById(social.id);
            const value = input ? input.value.trim() : '';
            
            // Check if it's a URL (starts with http:// or https://)
            const isUrl = value && (value.startsWith('http://') || value.startsWith('https://'));
            
            if (isUrl) {
                return `
                    <a href="#" class="profile-preview-link" style="background: rgba(255, 255, 255, 0.1); color: ${textColor};">
                        <img src="assets/img/${social.icon}" alt="${social.name}" class="social-icon-small">
                        <span>${social.name}</span>
                    </a>
                `;
            } else {
                // For banks/crypto (card numbers, wallet addresses), display as text
                return `
                    <div class="profile-preview-link" style="background: rgba(255, 255, 255, 0.1); color: ${textColor}; cursor: default;">
                        <img src="assets/img/${social.icon}" alt="${social.name}" class="social-icon-small">
                        <span>${social.name}: ${value}</span>
                    </div>
                `;
            }
        }).join('');
        
        // Apply text color to all links after rendering
        setTimeout(() => {
            const allLinks = previewLinks.querySelectorAll('.profile-preview-link');
            allLinks.forEach(link => {
                link.style.color = textColor;
            });
        }, 0);
    }

    showNotification(message, type = 'info') {
        // Use Utils if available, otherwise create simple notification
        if (typeof Utils !== 'undefined' && Utils.showNotification) {
            Utils.showNotification(message, type);
        } else {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };
            notification.style.backgroundColor = colors[type] || colors.info;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new CreateProfilePreview();
});

