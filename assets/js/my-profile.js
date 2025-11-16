class MyProfilePage {
    constructor() {
        console.log('MyProfilePage constructor called');
        this.profileData = null;
        this.companyData = null;
        this.uploadedFiles = {
            avatar: null,
            background: null,
            blockImage: null,
            socialBgImage: null,
            customLogo: null,
            file: null
        };
        this.customLogoSizeValueEl = null;
        this.logoPositionClasses = [
            'logo-position-top-left',
            'logo-position-top-center',
            'logo-position-top-right',
            'logo-position-middle-left',
            'logo-position-middle-center',
            'logo-position-middle-right',
            'logo-position-bottom-left',
            'logo-position-bottom-center',
            'logo-position-bottom-right'
        ];
        this.pendingFileAttachment = null;
        this.extraLinks = [];
        this.extraLinksContainer = null;
        this.extraLinksInitialized = false;
        this.extraLinkAddButton = null;
        const params = new URLSearchParams(window.location.search);
        this.mode = document.body.dataset.profileMode || params.get('mode') || 'default';
        if (this.mode === 'employee') {
            document.body.classList.add('employee-mode');
        }
        this.init(); 
    }

    init() {
        console.log('Initializing MyProfilePage...');
        this.checkAuthentication();
        this.setupEventListeners();
        this.loadProfile();
        this.setupCollapsibleSections();
    }

    checkAuthentication() {
        console.log('Checking authentication...');
        const userID = localStorage.getItem('userID');
        const password = localStorage.getItem('password');
        
        console.log('UserID:', userID);
        console.log('Password:', password ? 'Present' : 'Not present');
        
        if (!userID || !password) {
            console.log('No authentication found, redirecting to login');
            window.location.href = 'login.html';
            return;
        }
        
        console.log('Authentication found, proceeding...');
    }

    setupEventListeners() {
        console.log('Setting up event listeners...');
        
        // Form submission
        const form = document.getElementById('profileForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // File inputs
        const fileInputs = ['avatar', 'background', 'blockImage', 'blockImage2', 'socialBgImage', 'customLogo', 'fileUploadInput'];
        fileInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('change', (e) => this.handleFilePreview(e));
            }
        });

        const customLogoPositionInput = document.getElementById('customLogoPosition');
        if (customLogoPositionInput) {
            customLogoPositionInput.addEventListener('change', () => {
                if (!this.profileData) {
                    this.profileData = {};
                }
                this.profileData.customLogoPosition = customLogoPositionInput.value;
                this.updateCustomLogoStatus();
                this.updateCustomLogoPreview();
            });
        }

        const customLogoSizeInput = document.getElementById('customLogoSize');
        this.customLogoSizeValueEl = document.getElementById('customLogoSizeValue');
        if (customLogoSizeInput) {
            customLogoSizeInput.addEventListener('input', () => {
                if (this.customLogoSizeValueEl) {
                    this.customLogoSizeValueEl.textContent = customLogoSizeInput.value;
                }
                if (!this.profileData) {
                    this.profileData = {};
                }
                this.profileData.customLogoSize = Number(customLogoSizeInput.value);
                this.updateCustomLogoPreview();
            });
        }

        // Text inputs for live preview
        const textInputs = ['username', 'description'];
        textInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        const companyPositionInput = document.getElementById('companyPosition');
        if (companyPositionInput) {
            companyPositionInput.addEventListener('input', () => {
                if (!this.profileData) {
                    this.profileData = {};
                }
                this.profileData.companyTagline = companyPositionInput.value;
                this.updatePreview();
            });
        }

        // Enable text background checkbox handler
        const enableTextBgCheckbox = document.getElementById('enableTextBg');
        const textBgColorGroup = document.getElementById('textBgColorGroup');
        if (enableTextBgCheckbox && textBgColorGroup) {
            enableTextBgCheckbox.addEventListener('change', () => {
                const isEnabled = enableTextBgCheckbox.checked;
                textBgColorGroup.style.display = isEnabled ? 'block' : 'none';
                
                // Clear color if disabled
                if (!isEnabled) {
                    const textBgColorInput = document.getElementById('textBgColor');
                    if (textBgColorInput) {
                        textBgColorInput.value = '';
                    }
                }
                
                this.updatePreview();
            });
        }

        // Color inputs for live preview
        const colorInputs = ['profileColor', 'textColor', 'textBgColor'];
        colorInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        // Opacity sliders for live preview
        const opacityInputs = ['profileOpacity', 'textOpacity', 'textBgOpacity', 'socialOpacity'];
        opacityInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        // Social styling color inputs for live preview
        const socialColorInputs = ['socialBgColor', 'socialTextColor'];
        socialColorInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.updatePreview());
            }
        });

        // Social media inputs for live preview
        const socialInputs = [
            'instagram', 'youtube', 'tiktok', 'facebook', 'x', 'linkedin',
            'twitch', 'steam', 'discord', 'telegram',
            'spotify', 'soundcloud',
            'github', 'site',
            'googleDocs', 'googleSheets', 'fileUpload',
            'upwork', 'fiverr', 'djinni',
            'reddit', 'whatsapp', 'viber', 'youtubeMusic',
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

        const fileUploadField = document.getElementById('fileUpload');
        if (fileUploadField) {
            fileUploadField.addEventListener('input', () => {
                this.pendingFileAttachment = null;
                this.updateFileUploadStatus();
            });
        }

        // Description character counter
        const description = document.getElementById('description');
        if (description) {
            description.addEventListener('input', () => this.updateCharacterCounter());
        }

        // Copy link button
        const copyBtn = document.getElementById('copyLinkBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => this.copyProfileLink());
        }

        // Delete profile button
        const deleteBtn = document.getElementById('deleteProfileBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteProfile());
        }

        // Edit profile button - toggle editor
        const editBtn = document.getElementById('editProfileBtn');
        if (editBtn) {
            editBtn.addEventListener('click', () => this.openEditor());
        }

        // Close editor button
        const closeBtn = document.getElementById('closeEditorBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeEditor());
        }

        // Join company button
        const joinCompanyBtn = document.getElementById('joinCompanyBtn');
        if (joinCompanyBtn) {
            joinCompanyBtn.addEventListener('click', () => this.joinCompany());
        }

        // Company key input - uppercase
        const companyKeyInput = document.getElementById('companyKeyInput');
        if (companyKeyInput) {
            companyKeyInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase();
            });
        }

        // Toggle buttons for background types
        this.setupToggleButtons();

        // Initialize extra links UI
        this.initializeExtraLinksUI();

        console.log('Event listeners set up successfully');
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

    isStoredFilePath(value) {
        if (!value || typeof value !== 'string') {
            return false;
        }
        const normalized = value.trim().toLowerCase().replace(/\\/g, '/');
        return normalized.startsWith('uploads/profile/') || normalized.startsWith('/uploads/profile/');
    }

    formatFileUrl(value) {
        if (!value || typeof value !== 'string') {
            return '';
        }
        const trimmed = value.trim();
        const normalized = trimmed.replace(/\\/g, '/');

        if (normalized.startsWith('http://') || normalized.startsWith('https://')) {
            return normalized;
        }

        if (normalized.startsWith('/uploads/')) {
            return normalized;
        }

        if (normalized.startsWith('uploads/')) {
            return `/${normalized}`;
        }

        return normalized;
    }

    formatImageSource(value) {
        if (!value || typeof value !== 'string') {
            return null;
        }

        const trimmed = value.trim();
        if (trimmed === '') {
            return null;
        }

        const lower = trimmed.toLowerCase();
        if (lower === 'null' || lower === 'undefined') {
            return null;
        }

        const normalized = trimmed.replace(/\\/g, '/');

        if (normalized.startsWith('data:')) {
            return normalized;
        }

        if (normalized.startsWith('http://') || normalized.startsWith('https://')) {
            return normalized;
        }

        if (normalized.startsWith('/uploads/')) {
            return normalized;
        }

        if (normalized.startsWith('uploads/')) {
            return `/${normalized}`;
        }

        return `data:image/*;base64,${normalized}`;
    }

    getFileDisplayName(value) {
        if (!value || typeof value !== 'string') {
            return '';
        }
        const normalized = value.replace(/\\/g, '/');
        const segments = normalized.split('/');
        return segments.pop() || normalized;
    }

    formatFileSize(bytes) {
        if (typeof bytes !== 'number' || Number.isNaN(bytes) || bytes <= 0) {
            return '';
        }
        if (bytes < 1024) {
            return `${bytes} B`;
        }
        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }


    async loadProfile() {
        console.log('Loading profile...');
        console.log('localStorage userID:', localStorage.getItem('userID'));
        console.log('localStorage username:', localStorage.getItem('username'));
        console.log('localStorage password:', localStorage.getItem('password') ? 'Present' : 'Not present');
        
        try {
            const userID = localStorage.getItem('userID');
            const password = localStorage.getItem('password');
            const username = localStorage.getItem('username');
            
            if (!userID || !password) {
                console.log('No authentication data found');
                Utils.showNotification('Будь ласка, увійдіть в систему', 'info');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
                return;
            }

            // Try to get username from localStorage first
            let profileUsername = username;
            
            // If no username in localStorage, use userID (which should be the same)
            if (!profileUsername) {
                profileUsername = userID;
                console.log('Using userID as username:', profileUsername);
            }

            console.log('Loading profile for username:', profileUsername);

            const response = await fetch(`api/get-profile.php?username=${encodeURIComponent(profileUsername)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error text:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Profile data received:', data);

            if (data.success && data.profile) {
                this.profileData = data.profile;
                console.log('Profile loaded, avatar:', !!this.profileData.avatar, 'background:', !!this.profileData.background, 'bg:', !!this.profileData.bg);
                
                // Clear uploadedFiles when loading from server
                this.uploadedFiles = {
                    avatar: null,
                    background: null,
                    blockImage: null,
                    socialBgImage: null,
                    customLogo: null,
                    file: null
                };
                this.pendingFileAttachment = null;
                
                // Use setTimeout to ensure DOM is ready, especially if form is in collapsed section
                setTimeout(() => {
                    try {
                        this.populateForm();
                    } catch (e) {
                        console.error('Error in populateForm (delayed):', e);
                    }
                }, 100);
                
                // Use setTimeout to ensure DOM is ready
                setTimeout(() => {
                    try {
                        this.updatePreview();
                    } catch (e) {
                        console.warn('Error updating preview:', e);
                    }
                }, 200);
                this.setupProfileLink();
                console.log('Profile loaded successfully');
                
                // Load company info if user is in a company
                this.loadCompanyInfo();
            } else {
                const message = (data && data.message) ? data.message : 'Помилка завантаження профілю';
                console.error('Error loading profile:', message);
                Utils.showNotification(message, 'error');
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            const message = error && error.message ? error.message : 'Помилка завантаження профілю';
            Utils.showNotification(message, 'error');
        }
    }

    async loadCompanyInfo() {
        try {
            const username = localStorage.getItem('username');
            const password = localStorage.getItem('password');
            
            if (!username || !password) return;

            const response = await fetch(`api/get-company.php?username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
            const data = await response.json();

            if (data.success && data.company) {
                this.companyData = data.company;
                this.displayCompanyInfo();
                this.updatePreview();
                this.updateCompanyPositionFieldVisibility();
                // Hide join section
                const joinSection = document.getElementById('joinCompanySection');
                if (joinSection) {
                    joinSection.style.display = 'none';
                }
            } else {
                this.companyData = null;
                this.updatePreview();
                this.updateCompanyPositionFieldVisibility();
                const companyPositionInput = document.getElementById('companyPosition');
                if (companyPositionInput) {
                    companyPositionInput.value = '';
                }
                // Hide company section if user is not in a company
                const companySection = document.getElementById('companySection');
                if (companySection) {
                    companySection.style.display = 'none';
                }
                // Show join section
                const joinSection = document.getElementById('joinCompanySection');
                if (joinSection) {
                    joinSection.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error loading company info:', error);
        }
    }

    displayCompanyInfo() {
        const companySection = document.getElementById('companySection');
        const companyInfo = document.getElementById('companyInfo');
        
        if (!companySection || !companyInfo || !this.companyData) return;

        companySection.style.display = 'block';
        
        const isOwner = this.companyData.role === 'owner';
        const members = Array.isArray(this.companyData.members) ? this.companyData.members : [];

        let membersHtml = '';
        if (isOwner) {
            if (!members.length) {
                membersHtml = `
                    <div class="company-members">
                        <div class="company-members-header">
                            <h5>Учасники компанії</h5>
                        </div>
                        <div class="company-members-empty">Поки що в компанії тільки ви</div>
                    </div>
                `;
            } else {
                const items = members.map(member => {
                    const isOwnerMember = member.role === 'owner';
                    const badge = isOwnerMember ? '<span class="company-member-badge">Власник</span>' : '';
                    const position = member.company_tagline ? `<span class="company-member-position">${member.company_tagline}</span>` : '';
                    const actions = !isOwnerMember ? `<button type="button" class="btn btn-link-danger" data-action="remove-member" data-username="${member.username}">
                                Видалити
                            </button>` : '';
                    return `
                        <li class="company-member-item">
                            <div class="company-member-meta">
                                <span class="company-member-username">${member.username}</span>
                                ${badge}
                                ${position}
                            </div>
                            ${actions}
                        </li>
                    `;
                }).join('');

                membersHtml = `
                    <div class="company-members">
                        <div class="company-members-header">
                            <h5>Учасники компанії</h5>
                            <span class="company-members-count">${members.length}</span>
                        </div>
                        <ul class="company-members-list">
                            ${items}
                        </ul>
                    </div>
                `;
            }
        }

        companyInfo.innerHTML = `
            <div class="company-info-card">
                <div class="company-header">
                    <h4>${this.companyData.company_name}</h4>
                    <span class="company-badge">${isOwner ? 'Власник' : 'Учасник'}</span>
                </div>
                <div class="company-details">
                    <div class="company-detail">
                        <label>Ключ компанії:</label>
                        <div class="company-key-display">
                            <code>${this.companyData.company_key}</code>
                            <button type="button" class="btn btn-secondary btn-small" onclick="copyCompanyKey('${this.companyData.company_key}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                                Копіювати
                            </button>
                        </div>
                    </div>
                    <div class="company-detail">
                        <label>Учасників:</label>
                        <span>${this.companyData.members_count}</span>
                    </div>
                </div>
                ${isOwner ? `
                <div class="company-actions" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 0.75rem;">
                    <button type="button" id="deleteCompanyBtn" class="btn btn-danger btn-small">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3,6 5,6 21,6"/>
                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"/>
                        </svg>
                        Видалити компанію
                    </button>
                    <small style="display: block; color: var(--text-secondary); font-size: var(--font-size-sm);">
                        Видалення компанії призведе до видалення всіх учасників та перетворення їх профілів на особисті.
                    </small>
                </div>
                ${membersHtml}
                ` : membersHtml}
            </div>
        `;

        // Add owner-specific handlers
        if (isOwner) {
            // Add delete company button handler
            const deleteCompanyBtn = document.getElementById('deleteCompanyBtn');
            if (deleteCompanyBtn) {
                deleteCompanyBtn.addEventListener('click', () => this.deleteCompany());
            }

            const removeButtons = companyInfo.querySelectorAll('[data-action="remove-member"]');
            removeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const memberUsername = btn.dataset.username;
                    if (memberUsername) {
                        this.removeCompanyMember(memberUsername);
                    }
                });
            });
        }

        this.updateCompanyPositionFieldVisibility();
    }

    updateCompanyPositionFieldVisibility() {
        const positionGroup = document.getElementById('companyPositionGroup');
        if (!positionGroup) {
            return;
        }
        if (this.companyData) {
            positionGroup.style.display = 'block';
        } else {
            positionGroup.style.display = 'none';
        }
    }

    async removeCompanyMember(memberUsername) {
        if (!memberUsername || memberUsername === localStorage.getItem('username')) {
            return;
        }

        const confirmed = confirm(`Видалити користувача ${memberUsername} з компанії?`);
        if (!confirmed) {
            return;
        }

        const username = localStorage.getItem('username');
        const password = localStorage.getItem('password');

        if (!username || !password) {
            Utils.showNotification('Будь ласка, увійдіть в систему', 'error');
            return;
        }

        try {
            Utils.showLoading('Видалення учасника...');

            const response = await fetch('api/remove-company-member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username,
                    password,
                    memberUsername
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Не вдалося видалити учасника');
            }

            Utils.showNotification(data.message || 'Учасника видалено', 'success');
            await this.loadCompanyInfo();
        } catch (error) {
            console.error('Failed to remove company member:', error);
            Utils.showNotification(error.message || 'Помилка видалення учасника', 'error');
        } finally {
            Utils.hideLoading();
        }
    }

    async deleteCompany() {
        const confirmed = confirm('Ви впевнені, що хочете видалити компанію? Це призведе до:\n\n' +
            '• Видалення всіх учасників компанії\n' +
            '• Перетворення всіх профілів учасників на особисті\n' +
            '• Видалення всіх налаштувань компанії\n\n' +
            'Цю дію неможливо скасувати!');
        
        if (!confirmed) return;
        
        try {
            const username = localStorage.getItem('username');
            const password = localStorage.getItem('password');
            
            if (!username || !password) {
                Utils.showNotification('Будь ласка, увійдіть в систему', 'error');
                return;
            }
            
            Utils.showLoading('Видалення компанії...');
            
            const response = await fetch('api/delete-company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Utils.showNotification(data.message || 'Компанію успішно видалено!', 'success');
                
                // Reload company info after deletion
                setTimeout(() => {
                    this.loadCompanyInfo();
                }, 1000);
            } else {
                Utils.showNotification(data.message || 'Помилка видалення компанії', 'error');
            }
        } catch (error) {
            console.error('Error deleting company:', error);
            Utils.showNotification('Помилка з\'єднання', 'error');
        } finally {
            Utils.hideLoading();
        }
    }

    async joinCompany() {
        const companyKeyInput = document.getElementById('companyKeyInput');
        const joinCompanyError = document.getElementById('joinCompanyError');
        
        if (!companyKeyInput) return;
        
        const companyKey = companyKeyInput.value.trim().toUpperCase();
        
        if (!companyKey || companyKey.length !== 8) {
            if (joinCompanyError) {
                joinCompanyError.textContent = 'Введіть правильний ключ компанії (8 символів)';
                joinCompanyError.classList.add('show');
            }
            return;
        }
        
        try {
            const username = localStorage.getItem('username');
            const password = localStorage.getItem('password');
            
            if (!username || !password) {
                Utils.showNotification('Будь ласка, увійдіть в систему', 'error');
                return;
            }
            
            Utils.showLoading('Приєднання до компанії...');
            
            const response = await fetch('api/join-company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    company_key: companyKey
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Utils.showNotification('Успішно приєднано до компанії!', 'success');
                companyKeyInput.value = '';
                if (joinCompanyError) {
                    joinCompanyError.classList.remove('show');
                }
                
                // Reload company info
                setTimeout(() => {
                    this.loadCompanyInfo();
                }, 1000);
            } else {
                Utils.showNotification(data.message || 'Помилка приєднання', 'error');
                if (joinCompanyError) {
                    joinCompanyError.textContent = data.message || 'Помилка приєднання';
                    joinCompanyError.classList.add('show');
                }
            }
        } catch (error) {
            console.error('Error joining company:', error);
            Utils.showNotification('Помилка з\'єднання', 'error');
            if (joinCompanyError) {
                joinCompanyError.textContent = 'Помилка з\'єднання';
                joinCompanyError.classList.add('show');
            }
        } finally {
            Utils.hideLoading();
        }
    }

    tryLoadFromLocalStorage() {
        console.log('Trying to load profile from localStorage...');
        
        try {
            const userProfile = localStorage.getItem('userProfile');
            if (userProfile) {
                const profileData = JSON.parse(userProfile);
                console.log('Found profile data in localStorage:', profileData);
                
                // Convert localStorage data to expected format
                this.profileData = {
                    username: profileData.username,
                    description: profileData.description,
                    instagram: profileData.instagram,
                    discord: profileData.discord,
                    facebook: profileData.facebook,
                    steam: profileData.steam,
                    twitch: profileData.twitch,
                    tiktok: profileData.tiktok,
                    telegram: profileData.telegram,
                    youtube: profileData.youtube,
                    views: 0,
                    avatar: null,
                    bg: null,
                    color: '#c27eef',
                    colorText: '#ffffff'
                };
                
                this.populateForm();
                this.updatePreview();
                this.setupProfileLink();
                console.log('Profile loaded from localStorage successfully');
                
                Utils.showNotification('Profile loaded from cache. Some features may be limited.', 'warning');
            } else {
                console.log('No profile data found in localStorage');
                Utils.showNotification('Профіль не знайдено. Створіть новий профіль або перевірте базу даних.', 'info');
            }
        } catch (error) {
            console.error('Error loading from localStorage:', error);
            Utils.showNotification('Профіль не знайдено. Створіть новий профіль або перевірте базу даних.', 'info');
        }
    }

    populateForm() {
        try {
            console.log('Populating form with data:', this.profileData);
            
            if (!this.profileData) {
                console.log('No profile data to populate form');
                return;
            }

            // Проверяем, что форма существует в DOM
            const profileForm = document.getElementById('profileForm');
            if (!profileForm) {
                console.warn('Profile form not found in DOM, skipping form population');
                return;
            }

        // Basic information
        const usernameDisplay = document.getElementById('usernameDisplay');
        if (usernameDisplay) {
            usernameDisplay.textContent = this.profileData.username || 'Unknown';
        }
        
        const descriptionInput = document.getElementById('description');
        if (descriptionInput) {
            descriptionInput.value = this.profileData.description || this.profileData.descr || '';
        }

        const companyPositionInput = document.getElementById('companyPosition');
        if (companyPositionInput) {
            companyPositionInput.value = this.profileData.companyTagline || '';
        }

        try {
            this.updateCompanyPositionFieldVisibility();
        } catch (e) {
            console.warn('Error updating company position field visibility:', e);
        }

        // Colors - try both old and new field names for compatibility
        const profileColorInput = document.getElementById('profileColor');
        if (profileColorInput) {
            profileColorInput.value = this.profileData.profileColor || this.profileData.color || '#c27eef';
        }
        
        const textColorInput = document.getElementById('textColor');
        if (textColorInput) {
            textColorInput.value = this.profileData.textColor || this.profileData.colorText || '#ffffff';
        }
        
        // Text background color (optional) - check if enabled
        const textBgColor = this.profileData.textBgColor || '';
        const enableTextBg = textBgColor && textBgColor.trim() !== '';
        const enableTextBgCheckbox = document.getElementById('enableTextBg');
        const textBgColorGroup = document.getElementById('textBgColorGroup');
        
        if (enableTextBgCheckbox) {
            enableTextBgCheckbox.checked = enableTextBg;
            if (textBgColorGroup) {
                textBgColorGroup.style.display = enableTextBg ? 'block' : 'none';
            }
        }
        
        // Set text background color value
        const textBgColorInput = document.getElementById('textBgColor');
        if (textBgColorInput) {
            textBgColorInput.value = enableTextBg ? textBgColor : '';
        }
        
        // Opacity (with fallback to 100 if not set, minimum is 2)
        let profileOpacity = this.profileData.profileOpacity !== undefined ? this.profileData.profileOpacity : 100;
        let textOpacity = this.profileData.textOpacity !== undefined ? this.profileData.textOpacity : 100;
        let textBgOpacity = this.profileData.textBgOpacity !== undefined ? this.profileData.textBgOpacity : 100;
        
        // Ensure minimum value is 2
        if (profileOpacity < 2) profileOpacity = 2;
        if (textOpacity < 2) textOpacity = 2;
        if (textBgOpacity < 2) textBgOpacity = 2;
        
        const profileOpacityInput = document.getElementById('profileOpacity');
        const textOpacityInput = document.getElementById('textOpacity');
        const textBgOpacityInput = document.getElementById('textBgOpacity');
        
        if (!profileOpacityInput) {
            console.warn('profileOpacity input not found in DOM');
        } else {
            profileOpacityInput.value = profileOpacity;
        }
        
        if (!textOpacityInput) {
            console.warn('textOpacity input not found in DOM');
        } else {
            textOpacityInput.value = textOpacity;
        }
        
        if (!textBgOpacityInput) {
            console.warn('textBgOpacity input not found in DOM');
        } else {
            textBgOpacityInput.value = textBgOpacity;
        }
        
        // Update opacity values display
        const profileOpacityValueEl = document.getElementById('profileOpacityValue');
        const textOpacityValueEl = document.getElementById('textOpacityValue');
        const textBgOpacityValueEl = document.getElementById('textBgOpacityValue');
        if (profileOpacityValueEl) profileOpacityValueEl.textContent = profileOpacity + '%';
        if (textOpacityValueEl) textOpacityValueEl.textContent = textOpacity + '%';
        if (textBgOpacityValueEl) textBgOpacityValueEl.textContent = textBgOpacity + '%';
    
        // Social media - Popular Platforms (legacy static fields; guard in case they are removed)
        const instagramInput = document.getElementById('instagram');
        if (instagramInput) instagramInput.value = this.profileData.instagram || this.profileData.inst || '';
        const youtubeInput = document.getElementById('youtube');
        if (youtubeInput) youtubeInput.value = this.profileData.youtube || '';
        const tiktokInput = document.getElementById('tiktok');
        if (tiktokInput) tiktokInput.value = this.profileData.tiktok || '';
        const facebookInput = document.getElementById('facebook');
        if (facebookInput) facebookInput.value = this.profileData.facebook || this.profileData.fb || '';
        const xInput = document.getElementById('x');
        if (xInput) xInput.value = this.profileData.x || '';
        const linkedinInput = document.getElementById('linkedin');
        if (linkedinInput) linkedinInput.value = this.profileData.linkedin || '';
        
        // Gaming & Streaming
        const twitchInput = document.getElementById('twitch');
        if (twitchInput) twitchInput.value = this.profileData.twitch || '';
        const steamInput = document.getElementById('steam');
        if (steamInput) steamInput.value = this.profileData.steam || '';
        const discordInput = document.getElementById('discord');
        if (discordInput) discordInput.value = this.profileData.discord || '';
        const telegramInput = document.getElementById('telegram');
        if (telegramInput) telegramInput.value = this.profileData.telegram || this.profileData.tg || '';
        
        // Music & Audio
        const spotifyInput = document.getElementById('spotify');
        if (spotifyInput) spotifyInput.value = this.profileData.spotify || '';
        const soundcloudInput = document.getElementById('soundcloud');
        if (soundcloudInput) soundcloudInput.value = this.profileData.soundcloud || '';
        
        // Development & Tech
        const githubInput = document.getElementById('github');
        if (githubInput) githubInput.value = this.profileData.github || '';
        const siteInput = document.getElementById('site');
        if (siteInput) siteInput.value = this.profileData.site || '';

        // Documents & Files
        const googleDocsInput = document.getElementById('googleDocs');
        if (googleDocsInput) {
            googleDocsInput.value = this.profileData.googleDocs || '';
        }
        const googleSheetsInput = document.getElementById('googleSheets');
        if (googleSheetsInput) {
            googleSheetsInput.value = this.profileData.googleSheets || '';
        }
        const fileUploadField = document.getElementById('fileUpload');
        if (fileUploadField) {
            fileUploadField.value = this.profileData.fileUpload || '';
        }
        
        try {
            this.updateFileUploadStatus();
        } catch (e) {
            console.warn('Error updating file upload status:', e);
        }

        try {
            const profileExtraLinks = this.getExtraLinksFromProfile(this.profileData);
            this.setExtraLinks(profileExtraLinks);
        } catch (e) {
            console.warn('Error setting extra links:', e);
        }
        
        // Freelance & Work
        const upworkInput = document.getElementById('upwork');
        if (upworkInput) upworkInput.value = this.profileData.upwork || '';
        const fiverrInput = document.getElementById('fiverr');
        if (fiverrInput) fiverrInput.value = this.profileData.fiverr || '';
        const djinniInput = document.getElementById('djinni');
        if (djinniInput) djinniInput.value = this.profileData.djinni || '';
        
        // Other Platforms
        const redditInput = document.getElementById('reddit');
        if (redditInput) redditInput.value = this.profileData.reddit || '';
        const whatsappInput = document.getElementById('whatsapp');
        if (whatsappInput) whatsappInput.value = this.profileData.whatsapp || '';
        
        // New platforms
        const douInput = document.getElementById('dou');
        if (douInput) douInput.value = this.profileData.dou || '';
        const olxInput = document.getElementById('olx');
        if (olxInput) olxInput.value = this.profileData.olx || '';
        const amazonInput = document.getElementById('amazon');
        if (amazonInput) amazonInput.value = this.profileData.amazon || '';
        const promInput = document.getElementById('prom');
        if (promInput) promInput.value = this.profileData.prom || '';
        const fhuntInput = document.getElementById('fhunt');
        if (fhuntInput) fhuntInput.value = this.profileData.fhunt || '';
        const djInput = document.getElementById('dj');
        if (djInput) djInput.value = this.profileData.dj || '';
        
        // Banks
        const privatInput = document.getElementById('privatBank');
        if (privatInput) privatInput.value = this.profileData.privatBank || '';
        const monoInput = document.getElementById('monoBank');
        if (monoInput) monoInput.value = this.profileData.monoBank || '';
        const alfaInput = document.getElementById('alfaBank');
        if (alfaInput) alfaInput.value = this.profileData.alfaBank || '';
        const abankInput = document.getElementById('abank');
        if (abankInput) abankInput.value = this.profileData.abank || '';
        const pumbInput = document.getElementById('pumbBank');
        if (pumbInput) pumbInput.value = this.profileData.pumbBank || '';
        const raifInput = document.getElementById('raiffeisenBank');
        if (raifInput) raifInput.value = this.profileData.raiffeisenBank || '';
        const senseInput = document.getElementById('senseBank');
        if (senseInput) senseInput.value = this.profileData.senseBank || '';
        
        // Cryptocurrency
        const binanceInput = document.getElementById('binance');
        if (binanceInput) binanceInput.value = this.profileData.binance || '';
        const trustWalletInput = document.getElementById('trustWallet');
        if (trustWalletInput) trustWalletInput.value = this.profileData.trustWallet || '';

        // Social customization
        const socialBgColorInput = document.getElementById('socialBgColor');
        const socialTextColorInput = document.getElementById('socialTextColor');
        const socialOpacityInput = document.getElementById('socialOpacity');
        const socialOpacityValue = document.getElementById('socialOpacityValue');
        
        if (socialBgColorInput) {
            socialBgColorInput.value = this.profileData.socialBgColor || '#000000';
        }
        if (socialTextColorInput) {
            socialTextColorInput.value = this.profileData.socialTextColor || '#ffffff';
        }
        if (socialOpacityInput) {
            let socialOpacity = this.profileData.socialOpacity !== undefined ? this.profileData.socialOpacity : 90;
            if (socialOpacity < 2) socialOpacity = 2;
            socialOpacityInput.value = socialOpacity;
        }
        if (socialOpacityValue) {
            let socialOpacity = this.profileData.socialOpacity !== undefined ? this.profileData.socialOpacity : 90;
            if (socialOpacity < 2) socialOpacity = 2;
            socialOpacityValue.textContent = socialOpacity + '%';
        }

        const customLogoPositionInput = document.getElementById('customLogoPosition');
        if (customLogoPositionInput) {
            const position = this.profileData.customLogoPosition || 'none';
            customLogoPositionInput.value = position;
            this.profileData.customLogoPosition = position;
        }

        const customLogoSizeInput = document.getElementById('customLogoSize');
        let customLogoSize = this.profileData.customLogoSize !== undefined ? Number(this.profileData.customLogoSize) : 90;
        if (Number.isNaN(customLogoSize) || customLogoSize <= 0) {
            customLogoSize = 90;
        }
        if (customLogoSizeInput) {
            customLogoSizeInput.value = customLogoSize;
        }
        if (this.profileData) {
            this.profileData.customLogoSize = customLogoSize;
        }
        if (this.customLogoSizeValueEl) {
            this.customLogoSizeValueEl.textContent = customLogoSize;
        }

        try {
            this.updateCustomLogoStatus();
        } catch (e) {
            console.warn('Error updating custom logo status:', e);
        }

        // Update character counter
        try {
            this.updateCharacterCounter();
        } catch (e) {
            console.warn('Error updating character counter:', e);
        }
        
        // Force update preview after form is populated
        setTimeout(() => {
            try {
                this.updatePreview();
            } catch (e) {
                console.warn('Error updating preview:', e);
            }
        }, 100);
        } catch (error) {
            console.error('Error in populateForm:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                profileData: this.profileData
            });
            // Не прерываем выполнение, просто логируем ошибку
        }
    }

    updateFileUploadStatus() {
        const statusEl = document.getElementById('fileUploadStatus');
        if (!statusEl) {
            return;
        }

        let value = '';
        const input = document.getElementById('fileUpload');
        if (input && input.value) {
            value = input.value.trim();
        }

        if (!value && this.profileData && this.profileData.fileUpload) {
            value = this.profileData.fileUpload.trim();
        }

        if (this.pendingFileAttachment) {
            const sizeText = this.formatFileSize(this.pendingFileAttachment.size);
            const sizeLabel = sizeText ? ` (${sizeText})` : '';
            statusEl.textContent = `Обрано файл: ${this.pendingFileAttachment.name}${sizeLabel}. Збережіть зміни, щоб прикріпити.`;
            return;
        }

        if (value) {
            const isStored = this.isStoredFilePath(value);
            const href = this.formatFileUrl(value);
            const displayName = this.getFileDisplayName(value);

            if (isStored) {
                statusEl.innerHTML = `Поточний файл: <a href="${href}" target="_blank" rel="noopener noreferrer" download="${displayName}">${displayName}</a>`;
            } else {
                statusEl.innerHTML = `Поточне посилання: <a href="${href}" target="_blank" rel="noopener noreferrer">${href}</a>`;
            }
        } else {
            statusEl.textContent = '';
        }
    }

    updateCustomLogoStatus() {
        const statusEl = document.getElementById('customLogoStatus');
        if (!statusEl) {
            return;
        }

        const fileInput = document.getElementById('customLogo');
        const positionInput = document.getElementById('customLogoPosition');
        const position = positionInput ? positionInput.value : (this.profileData && this.profileData.customLogoPosition) || 'none';

        if (fileInput && fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            const sizeText = this.formatFileSize(file.size);
            const sizeLabel = sizeText ? ` (${sizeText})` : '';
            statusEl.textContent = `Обрано логотип: ${file.name}${sizeLabel}. Збережіть зміни, щоб застосувати.`;
            return;
        }

        if (this.uploadedFiles.customLogo) {
            statusEl.textContent = 'Логотип додано. Збережіть зміни, щоб застосувати.';
            return;
        }

        if (this.profileData && this.profileData.customLogo) {
            let storedPath = '';
            if (typeof this.profileData.customLogoPath === 'string') {
                const trimmed = this.profileData.customLogoPath.trim();
                if (trimmed && trimmed.toLowerCase() !== 'null' && trimmed.toLowerCase() !== 'undefined') {
                    storedPath = trimmed;
                }
            }
            const displayName = storedPath ? this.getFileDisplayName(storedPath) : '';
            const href = storedPath ? this.formatFileUrl(storedPath) : '';

            if (position === 'none') {
                if (storedPath && this.isStoredFilePath(storedPath)) {
                    statusEl.innerHTML = `Логотип <a href="${href}" target="_blank" rel="noopener noreferrer" download="${displayName}">${displayName}</a> збережено, але приховано.`;
                } else if (displayName) {
                    statusEl.textContent = `Логотип ${displayName} збережено, але приховано.`;
                } else {
                    statusEl.textContent = 'Логотип збережено, але наразі приховано. Оберіть позицію, щоб показати.';
                }
            } else if (storedPath && this.isStoredFilePath(storedPath)) {
                statusEl.innerHTML = `Поточний логотип: <a href="${href}" target="_blank" rel="noopener noreferrer" download="${displayName}">${displayName}</a>`;
            } else if (displayName) {
                statusEl.textContent = `Поточний логотип: ${displayName}`;
            } else {
                statusEl.textContent = 'Поточний логотип застосовано.';
            }
            return;
        }

        statusEl.textContent = 'Логотип не додано.';
    }

    updateCustomLogoPreview() {
        const logoWrapper = document.getElementById('previewCustomLogo');
        const logoImg = document.getElementById('previewCustomLogoImg');
        if (!logoWrapper || !logoImg) {
            return;
        }

        const positionInput = document.getElementById('customLogoPosition');
        const sizeInput = document.getElementById('customLogoSize');

        let position = positionInput
            ? positionInput.value
            : (this.profileData && this.profileData.customLogoPosition) || 'none';
        const requestedPosition = position;
        let normalizedPosition = `logo-position-${position}`;
        if (!this.logoPositionClasses.includes(normalizedPosition)) {
            position = 'middle-center';
            normalizedPosition = 'logo-position-middle-center';
        }

        let size = sizeInput
            ? Number(sizeInput.value)
            : (this.profileData && Number(this.profileData.customLogoSize)) || 90;
        if (Number.isNaN(size) || size <= 0) {
            size = 90;
        }
        size = Math.round(size);

        let source = null;
        if (this.uploadedFiles.customLogo) {
            source = this.uploadedFiles.customLogo;
        } else if (this.profileData && this.profileData.customLogo) {
            source = this.formatImageSource(this.profileData.customLogo);
        }

        logoWrapper.classList.remove('logo-visible', ...this.logoPositionClasses);
        logoWrapper.style.display = 'none';
        logoWrapper.style.width = '';
        logoImg.src = '';

        if (!source || requestedPosition === 'none') {
            return;
        }

        logoWrapper.style.display = 'block';
        logoWrapper.style.width = `${size}px`;
        logoWrapper.classList.add('logo-visible');
        logoWrapper.classList.add(normalizedPosition);
        logoImg.src = source;
    }

    updatePreview() {
        console.log('Updating preview...');
        
        // Update name and description
        const previewName = document.getElementById('previewName');
        const previewDescription = document.getElementById('previewDescription');
        
        if (previewName) {
            const usernameDisplay = document.getElementById('usernameDisplay');
            const username = usernameDisplay ? usernameDisplay.textContent : (this.profileData ? this.profileData.username : 'Username');
            previewName.textContent = username;
        }
        
        if (previewDescription) {
            const description = document.getElementById('description').value || 'No description';
            previewDescription.textContent = description;
        }

        // Update avatar - check uploadedFiles first, then profileData
        const previewAvatarImg = document.getElementById('previewAvatarImg');
        if (previewAvatarImg) {
            if (this.uploadedFiles.avatar) {
                // Use newly uploaded file
                previewAvatarImg.src = this.uploadedFiles.avatar;
                previewAvatarImg.style.display = 'block';
            } else if (this.profileData && this.profileData.avatar) {
                // Use existing profile data
                previewAvatarImg.src = `data:image/jpeg;base64,${this.profileData.avatar}`;
                previewAvatarImg.style.display = 'block';
            } else {
                // Use default
                previewAvatarImg.src = 'assets/img/profile.png';
            }
        }

        // Update background (container) - check uploadedFiles first, then profileData
        const container = document.getElementById('profilePreviewContainer');
        if (container) {
            if (this.uploadedFiles.background) {
                // Use newly uploaded file
                container.style.setProperty('background-image', `url(${this.uploadedFiles.background})`, 'important');
                container.style.setProperty('background-size', 'cover', 'important');
                container.style.setProperty('background-position', 'center', 'important');
                container.style.setProperty('background-repeat', 'no-repeat', 'important');
                container.classList.add('has-background-image');
            } else if (this.profileData && (this.profileData.background || this.profileData.bg)) {
                // Use existing profile data
                const backgroundImage = this.profileData.background || this.profileData.bg;
                container.style.setProperty('background-image', `url(data:image/jpeg;base64,${backgroundImage})`, 'important');
                container.style.setProperty('background-size', 'cover', 'important');
                container.style.setProperty('background-position', 'center', 'important');
                container.style.setProperty('background-repeat', 'no-repeat', 'important');
                container.classList.add('has-background-image');
            } else {
                // Clear background
                container.style.removeProperty('background-image');
                container.classList.remove('has-background-image');
            }
        }

        // Update block image (card) - check uploadedFiles first, then profileData
        const card = document.getElementById('profilePreviewCard');
        if (card) {
            if (this.uploadedFiles.blockImage) {
                // Use newly uploaded file
                card.style.setProperty('background-image', `url(${this.uploadedFiles.blockImage})`, 'important');
                card.style.setProperty('background-size', 'cover', 'important');
                card.style.setProperty('background-position', 'center', 'important');
                card.style.setProperty('background-repeat', 'no-repeat', 'important');
            } else if (this.profileData && (this.profileData.blockImage || this.profileData.block_image)) {
                // Use existing profile data
                const blockImage = this.profileData.blockImage || this.profileData.block_image;
                card.style.setProperty('background-image', `url(data:image/jpeg;base64,${blockImage})`, 'important');
                card.style.setProperty('background-size', 'cover', 'important');
                card.style.setProperty('background-position', 'center', 'important');
                card.style.setProperty('background-repeat', 'no-repeat', 'important');
            } else {
                // Don't clear block image background if there's a color set
                // The color will be applied separately
            }
        }

        // Update colors with opacity
        const profileColorInput = document.getElementById('profileColor');
        const textColorInput = document.getElementById('textColor');
        const textBgColorInput = document.getElementById('textBgColor');
        const profileOpacityInput = document.getElementById('profileOpacity');
        const textOpacityInput = document.getElementById('textOpacity');
        const textBgOpacityInput = document.getElementById('textBgOpacity');
        
        if (profileColorInput && textColorInput && profileOpacityInput && textOpacityInput) {
            const profileColor = profileColorInput.value;
            const textColor = textColorInput.value;
            const textBgColor = textBgColorInput ? textBgColorInput.value : '';
            const profileOpacity = profileOpacityInput.value;
            const textOpacity = textOpacityInput.value;
            const textBgOpacity = textBgOpacityInput ? textBgOpacityInput.value : 100;
            
            // Convert hex to rgba with opacity
            const profileColorRgba = this.hexToRgba(profileColor, profileOpacity / 100);
            const textColorRgba = this.hexToRgba(textColor, textOpacity / 100);
            
            const card = document.getElementById('profilePreviewCard');
            if (card) {
                card.style.setProperty('background-color', profileColorRgba, 'important');
            }
            
            // Apply text color
            if (previewName) {
                previewName.style.setProperty('color', textColorRgba, 'important');
            }
            if (previewDescription) {
                previewDescription.style.setProperty('color', textColorRgba, 'important');
            }
            
            // Apply text background only if enabled via checkbox
            const enableTextBgCheckbox = document.getElementById('enableTextBg');
            const isTextBgEnabled = enableTextBgCheckbox ? enableTextBgCheckbox.checked : false;
            
            if (isTextBgEnabled && textBgColor && textBgColor.trim() !== '') {
                const textBgColorRgba = this.hexToRgba(textBgColor, textBgOpacity / 100);
                
                if (previewName) {
                    previewName.style.setProperty('background-color', textBgColorRgba, 'important');
                    previewName.style.setProperty('padding', '4px 8px', 'important');
                    previewName.style.setProperty('border-radius', '4px', 'important');
                    previewName.style.setProperty('box-decoration-break', 'clone', 'important');
                }
                if (previewDescription) {
                    previewDescription.style.setProperty('background-color', textBgColorRgba, 'important');
                    previewDescription.style.setProperty('padding', '4px 8px', 'important');
                    previewDescription.style.setProperty('border-radius', '4px', 'important');
                    previewDescription.style.setProperty('box-decoration-break', 'clone', 'important');
                }
                // Apply same text background to company name and tagline in preview
                const previewCompanyNameEl = document.getElementById('previewCompanyName');
                const previewCompanyTaglineEl = document.getElementById('previewCompanyTagline');
                if (previewCompanyNameEl) {
                    previewCompanyNameEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    previewCompanyNameEl.style.setProperty('padding', '4px 8px', 'important');
                    previewCompanyNameEl.style.setProperty('border-radius', '4px', 'important');
                    previewCompanyNameEl.style.setProperty('box-decoration-break', 'clone', 'important');
                }
                if (previewCompanyTaglineEl) {
                    previewCompanyTaglineEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    previewCompanyTaglineEl.style.setProperty('padding', '4px 8px', 'important');
                    previewCompanyTaglineEl.style.setProperty('border-radius', '4px', 'important');
                    previewCompanyTaglineEl.style.setProperty('box-decoration-break', 'clone', 'important');
                }
            } else {
                // Remove text background if disabled or not set
                if (previewName) {
                    previewName.style.setProperty('background-color', 'transparent', 'important');
                    previewName.style.removeProperty('padding');
                    previewName.style.removeProperty('border-radius');
                    previewName.style.removeProperty('box-decoration-break');
                }
                if (previewDescription) {
                    previewDescription.style.setProperty('background-color', 'transparent', 'important');
                    previewDescription.style.removeProperty('padding');
                    previewDescription.style.removeProperty('border-radius');
                    previewDescription.style.removeProperty('box-decoration-break');
                }
                const previewCompanyNameEl = document.getElementById('previewCompanyName');
                const previewCompanyTaglineEl = document.getElementById('previewCompanyTagline');
                if (previewCompanyNameEl) {
                    previewCompanyNameEl.style.setProperty('background-color', 'transparent', 'important');
                    previewCompanyNameEl.style.removeProperty('padding');
                    previewCompanyNameEl.style.removeProperty('border-radius');
                    previewCompanyNameEl.style.removeProperty('box-decoration-break');
                }
                if (previewCompanyTaglineEl) {
                    previewCompanyTaglineEl.style.setProperty('background-color', 'transparent', 'important');
                    previewCompanyTaglineEl.style.removeProperty('padding');
                    previewCompanyTaglineEl.style.removeProperty('border-radius');
                    previewCompanyTaglineEl.style.removeProperty('box-decoration-break');
                }
            }
            
            // Update opacity value displays
            document.getElementById('profileOpacityValue').textContent = profileOpacity + '%';
            document.getElementById('textOpacityValue').textContent = textOpacity + '%';
            if (textBgOpacityInput) {
                document.getElementById('textBgOpacityValue').textContent = textBgOpacity + '%';
            }

            const branding = this.resolveCompanyBranding();
            const companyIdentity = document.getElementById('previewCompanyIdentity');
            const companyNameEl = document.getElementById('previewCompanyName');
            const companyTaglineEl = document.getElementById('previewCompanyTagline');
            const companyLogoWrapper = document.getElementById('previewCompanyLogoWrapper');
            const companyLogoImg = document.getElementById('previewCompanyLogo');

            if (companyIdentity && companyNameEl && companyTaglineEl && companyLogoWrapper && companyLogoImg) {
                const hasName = branding.name && branding.name.trim() !== '';
                const hasTagline = branding.tagline && branding.tagline.trim() !== '';
                const showName = branding.showName && hasName;
                const showLogo = branding.showLogo && !!branding.logo;

                if (showName) {
                    companyNameEl.textContent = branding.name;
                    companyNameEl.style.display = 'block';
                } else {
                    companyNameEl.textContent = '';
                    companyNameEl.style.display = 'none';
                }

                if (hasTagline) {
                    companyTaglineEl.textContent = branding.tagline;
                    companyTaglineEl.style.display = 'block';
                } else {
                    companyTaglineEl.textContent = '';
                    companyTaglineEl.style.display = 'none';
                }

                if (showLogo) {
                    companyLogoWrapper.style.display = 'flex';
                    const formattedLogo = this.formatImageSource(branding.logo);
                    if (formattedLogo) {
                        companyLogoImg.src = formattedLogo;
                    }
                } else {
                    companyLogoWrapper.style.display = 'none';
                    companyLogoImg.src = '';
                }

                const identityVisible = showName || showLogo || hasTagline;
                companyIdentity.style.display = identityVisible ? 'flex' : 'none';

                companyNameEl.style.setProperty('color', textColorRgba, 'important');
                companyTaglineEl.style.setProperty('color', textColorRgba, 'important');
            }
        }

        this.updateCustomLogoPreview();

        // Update social links
        this.updatePreviewLinks();
        
        // Apply social link styling AFTER links are created
        setTimeout(() => {
            const socialBgColorInput = document.getElementById('socialBgColor');
            const socialTextColorInput = document.getElementById('socialTextColor');
            const socialOpacityInput = document.getElementById('socialOpacity');
            
            if (socialBgColorInput && socialTextColorInput && socialOpacityInput) {
                const socialBgColor = socialBgColorInput.value || '#000000';
                const socialTextColor = socialTextColorInput.value || '#ffffff';
                const socialOpacity = socialOpacityInput.value || 90;
                
                const socialBgColorRgba = this.hexToRgba(socialBgColor, socialOpacity / 100);
                
                // Update social links in preview
                const previewLinks = document.getElementById('previewLinks');
                if (previewLinks) {
                    const socialLinks = previewLinks.querySelectorAll('.profile-preview-link');
                    
                    socialLinks.forEach(link => {
                        link.style.setProperty('background-color', socialBgColorRgba, 'important');
                        link.style.setProperty('color', socialTextColor, 'important');
                    });
                }
            }
        }, 0);
        
        console.log('Preview updated successfully');
    }

    updatePreviewLinks() {
        console.log('Updating preview links...');
        
        const linksContainer = document.getElementById('previewLinks');
        if (!linksContainer) {
            console.log('Links container not found');
            return;
        }
        
        const socialLinks = this.getSocialLinks();
        console.log('Social links found:', socialLinks);
        
        if (socialLinks.length === 0) {
            linksContainer.innerHTML = `<p style="color: var(--text-muted); font-size: var(--font-size-sm);">No links added</p>`;
            return;
        }

        const baseStyle = 'background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);';

        const linksHTML = socialLinks.map(link => {
            const label = link.displayName || link.name;
            if (link.href) {
                const attributes = [];
                if (link.isFile) {
                    const downloadName = link.downloadName || '';
                    attributes.push(`download="${downloadName}"`);
                } else {
                    attributes.push('target="_blank"');
                    attributes.push('rel="noopener noreferrer"');
                }

                return `
                    <a href="${link.href}" class="profile-preview-link" style="${baseStyle}" ${attributes.join(' ')}>
                        <img src="${link.icon}" alt="${link.name}" class="social-icon-small">
                        <span>${label}</span>
                    </a>
                `;
            }

            const valueText = link.displayValue || link.url || '';
            return `
                <div class="profile-preview-link" style="${baseStyle} cursor: default;">
                    <img src="${link.icon}" alt="${link.name}" class="social-icon-small">
                    <span>${valueText ? `${link.name}: ${valueText}` : label}</span>
                </div>
            `;
        }).join('');

        linksContainer.innerHTML = linksHTML;
        console.log('Preview links updated successfully');
    }

    getSocialPlatformDefinitions() {
        return {
            // Popular Platforms
            instagram: { name: 'Instagram', icon: 'assets/img/insta.png', category: 'social' },
            youtube: { name: 'YouTube', icon: 'assets/img/youtube.png', category: 'social' },
            tiktok: { name: 'TikTok', icon: 'assets/img/tiktok.png', category: 'social' },
            facebook: { name: 'Facebook', icon: 'assets/img/facebook.png', category: 'social' },
            x: { name: 'X (Twitter)', icon: 'assets/img/x.png', category: 'social' },
            linkedin: { name: 'LinkedIn', icon: 'assets/img/linkedin.png', category: 'social' },
            reddit: { name: 'Reddit', icon: 'assets/img/reddit.png', category: 'social' },
            
            // Messaging & Chats
            telegram: { name: 'Telegram', icon: 'assets/img/tg.png', category: 'messenger' },
            whatsapp: { name: 'WhatsApp', icon: 'assets/img/whatsapp.png', category: 'messenger' },
            viber: { name: 'Viber', icon: 'assets/img/viber.png', category: 'messenger' },
            discord: { name: 'Discord', icon: 'assets/img/discord.png', category: 'messenger' },
            
            // Gaming & Streaming
            twitch: { name: 'Twitch', icon: 'assets/img/twitch.png', category: 'gaming' },
            steam: { name: 'Steam', icon: 'assets/img/steam.png', category: 'gaming' },
            
            // Music & Audio
            spotify: { name: 'Spotify', icon: 'assets/img/spotify.png', category: 'music' },
            soundcloud: { name: 'SoundCloud', icon: 'assets/img/soundcloud.png', category: 'music' },
            youtubeMusic: { name: 'YouTube Music', icon: 'assets/img/youtubeMusic.png', category: 'music' },
            
            // Development & Tech
            github: { name: 'GitHub', icon: 'assets/img/github.png', category: 'tech' },
            site: { name: 'Website', icon: 'assets/img/site.png', category: 'tech' },
            
            // Documents & Files
            googleDocs: { name: 'Google Docs', icon: 'assets/img/google docs.png', category: 'documents' },
            googleSheets: { name: 'Google Sheets', icon: 'assets/img/googlesheets.png', category: 'documents' },
            fileUpload: { name: 'Файл', icon: 'assets/img/file.png', category: 'documents' },
            
            // Freelance & Work
            upwork: { name: 'Upwork', icon: 'assets/img/upwork.png', category: 'work' },
            fiverr: { name: 'Fiverr', icon: 'assets/img/fiverr.png', category: 'work' },
            djinni: { name: 'Djinni', icon: 'assets/img/djinni.png', category: 'work' },
            dou: { name: 'DOU', icon: 'assets/img/dou.png', category: 'work' },
            
            // Other Platforms
            olx: { name: 'OLX', icon: 'assets/img/olx.png', category: 'marketplace' },
            amazon: { name: 'Amazon', icon: 'assets/img/amazon.png', category: 'marketplace' },
            prom: { name: 'Prom.ua', icon: 'assets/img/prom.png', category: 'marketplace' },
            fhunt: { name: 'FHunt', icon: 'assets/img/fhunt.png', category: 'marketplace' },
            dj: { name: 'DJ', icon: 'assets/img/dj.png', category: 'marketplace' },
            
            // Banks
            privatBank: { name: 'ПриватБанк', icon: 'assets/img/privatBank.png', category: 'bank' },
            monoBank: { name: 'Монобанк', icon: 'assets/img/monoBank.png', category: 'bank' },
            alfaBank: { name: 'Альфа-Банк', icon: 'assets/img/alfaBank.png', category: 'bank' },
            abank: { name: 'А-Банк', icon: 'assets/img/abank.png', category: 'bank' },
            pumbBank: { name: 'ПУМБ', icon: 'assets/img/pumbBank.png', category: 'bank' },
            raiffeisenBank: { name: 'Райффайзен Банк', icon: 'assets/img/raiffeisenBank.png', category: 'bank' },
            senseBank: { name: 'Sense Bank', icon: 'assets/img/senseBank.png', category: 'bank' },
            
            // Cryptocurrency Exchanges
            binance: { name: 'Binance', icon: 'assets/img/binance.png', category: 'crypto' },
            trustWallet: { name: 'Trust Wallet', icon: 'assets/img/trustWallet.png', category: 'crypto' }
        };
    }

    getSocialLinks() {
        console.log('Getting social links...');
        
        const links = [];
        const socialPlatforms = this.getSocialPlatformDefinitions();

        Object.entries(socialPlatforms).forEach(([key, platform]) => {
            const input = document.getElementById(key);
            let value = input && typeof input.value === 'string' ? input.value.trim() : '';

            if (key === 'fileUpload') {
                if (this.pendingFileAttachment) {
                    const sizeText = this.formatFileSize(this.pendingFileAttachment.size);
                    const pendingLabel = sizeText
                        ? `${this.pendingFileAttachment.name} (${sizeText})`
                        : this.pendingFileAttachment.name;
                    links.push({
                        ...platform,
                        url: '',
                        href: '',
                        displayName: `${platform.name}: ${pendingLabel}`,
                        displayValue: `Файл буде прикріплено після збереження (${pendingLabel})`,
                        isPendingFile: true
                    });
                    return;
                }

                if (!value && this.profileData && this.profileData.fileUpload) {
                    value = String(this.profileData.fileUpload).trim();
                }

                if (value) {
                    const isStored = this.isStoredFilePath(value);
                    const href = isStored ? this.formatFileUrl(value) : this.normalizeUrl(value, key);
                    const displayName = isStored
                        ? `${platform.name}: ${this.getFileDisplayName(value)}`
                        : platform.name;

                    links.push({
                        ...platform,
                        url: value,
                        href,
                        isFile: isStored,
                        displayName,
                        displayValue: isStored ? this.getFileDisplayName(value) : value,
                        downloadName: isStored ? this.getFileDisplayName(value) : null
                    });
                }
                return;
            }

            if (!value && this.profileData) {
                const fallbackValue = this.profileData[key];
                if (typeof fallbackValue === 'string' && fallbackValue.trim() !== '') {
                    value = fallbackValue.trim();
                } else if (typeof fallbackValue === 'number') {
                    value = String(fallbackValue);
                } else {
                    if (key === 'instagram' && this.profileData.inst) value = String(this.profileData.inst).trim();
                    if (key === 'facebook' && this.profileData.fb) value = String(this.profileData.fb).trim();
                    if (key === 'telegram' && this.profileData.tg) value = String(this.profileData.tg).trim();
                }
            }

            if (value) {
                const normalizedUrl = this.normalizeUrl(value, key);
                const isUrl = normalizedUrl.startsWith('http://') || normalizedUrl.startsWith('https://');
                links.push({
                    ...platform,
                    url: normalizedUrl,
                    href: isUrl ? normalizedUrl : '',
                    displayName: platform.name,
                    displayValue: normalizedUrl
                });
            }
        });

        if (Array.isArray(this.extraLinks) && this.extraLinks.length > 0) {
            this.extraLinks.forEach((entry) => {
                if (!entry || entry.removed) {
                    return;
                }

                const platformKey = entry.platform && socialPlatforms[entry.platform] ? entry.platform : null;
                const basePlatform = platformKey
                    ? socialPlatforms[platformKey]
                    : {
                        name: entry.label && entry.label.trim() ? entry.label.trim() : 'Посилання',
                        icon: (socialPlatforms.site && socialPlatforms.site.icon) || 'assets/img/site.png'
                    };

                const customLabel = entry.label && entry.label.trim() ? entry.label.trim() : '';

                if (entry.type === 'file') {
                    if (entry.pendingFile instanceof File) {
                        const sizeText = this.formatFileSize(entry.pendingFile.size);
                        const pendingLabel = sizeText
                            ? `${entry.pendingFile.name} (${sizeText})`
                            : entry.pendingFile.name;
                        const labelText = customLabel || basePlatform.name;

                        links.push({
                            ...basePlatform,
                            url: '',
                            href: '',
                            displayName: `${labelText}: ${pendingLabel}`,
                            displayValue: `Файл буде прикріплено після збереження (${pendingLabel})`,
                            isPendingFile: true
                        });
                        return;
                    }

                    if (entry.storedFilePath) {
                        const href = this.formatFileUrl(entry.storedFilePath);
                        const fileName = entry.originalName || this.getFileDisplayName(entry.storedFilePath);
                        const labelText = customLabel ? `${customLabel}: ${fileName}` : `${basePlatform.name}: ${fileName}`;

                        links.push({
                            ...basePlatform,
                            url: entry.storedFilePath,
                            href,
                            displayName: labelText,
                            displayValue: fileName,
                            isFile: true,
                            downloadName: fileName
                        });
                    }
                    return;
                }

                const value = entry.url ? entry.url.trim() : '';
                if (!value) {
                    return;
                }

                const normalizedUrl = this.normalizeUrl(value, entry.platform || '');
                const isUrl = normalizedUrl.startsWith('http://') || normalizedUrl.startsWith('https://');
                const labelText = customLabel || basePlatform.name;

                links.push({
                    ...basePlatform,
                    url: normalizedUrl,
                    href: isUrl ? normalizedUrl : '',
                    displayName: labelText,
                    displayValue: normalizedUrl
                });
            });
        }

        console.log('Social links result:', links);
        return links;
    }

    ensureExtraLinksInitialized() {
        if (!this.extraLinksInitialized) {
            this.initializeExtraLinksUI();
        }
    }

    setupExtraLinkPicker() {
        const picker = document.getElementById('extraLinkPicker');
        const searchInput = document.getElementById('extraLinkSearch');
        const platformsContainer = document.getElementById('extraLinkPlatforms');
        const addBtn = document.getElementById('addExtraLinkBtn');
        const categoryFilters = document.getElementById('extraLinkCategoryFilters');

        if (!picker || !searchInput || !platformsContainer || !addBtn) {
            console.warn('Extra link picker elements not found');
            return;
        }

        if (!categoryFilters) {
            console.error('Category filters container not found! Element #extraLinkCategoryFilters is missing in DOM');
            return;
        }

        console.log('Setting up category filters...');

        const platforms = this.getSocialPlatformDefinitions();
        
        // Категории для фильтров
        const categories = {
            'all': 'Всі',
            'social': 'Соціальні мережі',
            'messenger': 'Месенджери',
            'gaming': 'Ігри та стримінг',
            'music': 'Музика',
            'tech': 'Технології',
            'documents': 'Документи та файли',
            'work': 'Робота та фріланс',
            'marketplace': 'Маркетплейси',
            'bank': 'Банки',
            'crypto': 'Криптовалюта'
        };

        let selectedCategories = new Set(['all']);

        // Функция для создания фильтров категорий
        const createCategoryFilters = () => {
            if (!categoryFilters) {
                console.error('Cannot create filters: categoryFilters element is null');
                return;
            }
            
            console.log('Creating category filters...', categoryFilters);
            
            const filtersHTML = Object.entries(categories).map(([key, label]) => {
                const isActive = selectedCategories.has(key);
                return `
                    <button type="button" class="category-filter-btn ${isActive ? 'active' : ''}" data-category="${key}">
                        ${label}
                    </button>
                `;
            }).join('');
            
            categoryFilters.innerHTML = filtersHTML;
            console.log('Category filters HTML created, buttons count:', Object.keys(categories).length);

            categoryFilters.querySelectorAll('.category-filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const category = btn.dataset.category;
                    
                    if (category === 'all') {
                        // Если кликнули на "Всі", сбрасываем все фильтры
                        selectedCategories.clear();
                        selectedCategories.add('all');
                        categoryFilters.querySelectorAll('.category-filter-btn').forEach(b => {
                            b.classList.toggle('active', b.dataset.category === 'all');
                        });
                    } else {
                        // Убираем "Всі" если выбрана другая категория
                        selectedCategories.delete('all');
                        const allBtn = categoryFilters.querySelector('[data-category="all"]');
                        if (allBtn) {
                            allBtn.classList.remove('active');
                        }
                        
                        // Переключаем выбранную категорию
                        if (selectedCategories.has(category)) {
                            // Убираем из выбранных
                            selectedCategories.delete(category);
                            btn.classList.remove('active');
                            console.log('Filter deselected:', category);
                        } else {
                            // Добавляем в выбранные
                            selectedCategories.add(category);
                            btn.classList.add('active');
                            console.log('Filter selected:', category);
                        }
                        
                        // Если ничего не выбрано, выбираем "Всі"
                        if (selectedCategories.size === 0) {
                            selectedCategories.add('all');
                            if (allBtn) {
                                allBtn.classList.add('active');
                            }
                        }
                    }
                    
                    // Обновляем визуальное состояние всех кнопок
                    categoryFilters.querySelectorAll('.category-filter-btn').forEach(b => {
                        const cat = b.dataset.category;
                        if (selectedCategories.has(cat)) {
                            b.classList.add('active');
                        } else {
                            b.classList.remove('active');
                        }
                    });
                    
                    console.log('Selected categories:', Array.from(selectedCategories));
                    renderPlatforms(searchInput.value, selectedCategories);
                });
            });
        };

        // Создаем фильтры при инициализации
        createCategoryFilters();
        console.log('Category filters initialized');

        const renderPlatforms = (filter = '', categoriesSet = new Set(['all'])) => {
            const term = filter.trim().toLowerCase();
            const entries = Object.entries(platforms)
                .filter(([key, platform]) => {
                    // Фильтр по категориям (можно выбрать несколько)
                    if (categoriesSet.has('all')) {
                        // Если выбрано "Всі", показываем все
                    } else {
                        // Иначе показываем только выбранные категории
                        if (!categoriesSet.has(platform.category)) {
                            return false;
                        }
                    }
                    // Фильтр по поиску
                    if (!term) return true;
                    const name = (platform.name || '').toLowerCase();
                    return name.includes(term) || key.toLowerCase().includes(term);
                });

            if (entries.length === 0) {
                platformsContainer.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--text-muted);">Нічого не знайдено</p>';
                return;
            }

            platformsContainer.innerHTML = entries.map(([key, platform]) => `
                <button type="button" class="extra-link-platform" data-platform="${key}">
                    <img src="${platform.icon}" alt="${platform.name}" class="extra-link-platform-icon">
                    <span class="extra-link-platform-name">${platform.name}</span>
                </button>
            `).join('');

            platformsContainer.querySelectorAll('.extra-link-platform').forEach(btn => {
                btn.addEventListener('click', () => {
                    const platformKey = btn.dataset.platform;
                    picker.style.display = 'none';
                    this.addExtraLink({ platform: platformKey });
                });
            });
        };

        addBtn.addEventListener('click', () => {
            if (picker.style.display === 'none' || picker.style.display === '') {
                // Убеждаемся, что элемент categoryFilters доступен
                const currentCategoryFilters = document.getElementById('extraLinkCategoryFilters');
                if (!currentCategoryFilters) {
                    console.error('Category filters element not found when opening picker!');
                }
                
                // Создаем фильтры заново при открытии (на случай если DOM изменился)
                selectedCategories.clear();
                selectedCategories.add('all');
                createCategoryFilters();
                
                // Проверяем, что фильтры созданы
                const filterButtons = categoryFilters.querySelectorAll('.category-filter-btn');
                console.log('Filter buttons created:', filterButtons.length);
                
                renderPlatforms('', selectedCategories);
                picker.style.display = 'block';
                searchInput.value = '';
                searchInput.focus();
            } else {
                picker.style.display = 'none';
            }
        });

        searchInput.addEventListener('input', () => {
            renderPlatforms(searchInput.value, selectedCategories);
        });
    }

    setupCollapsibleSections() {
        const sections = document.querySelectorAll('.form-section.collapsible-section');
        sections.forEach(section => {
            const header = section.querySelector('h3');
            if (!header) return;
            header.addEventListener('click', () => {
                section.classList.toggle('collapsed');
            });
        });
    }

    initializeExtraLinksUI() {
        if (this.extraLinksInitialized) {
            return;
        }

        const container = document.getElementById('extraLinksContainer');
        const addBtn = document.getElementById('addExtraLinkBtn');

        if (!container || !addBtn) {
            console.warn('Extra links UI elements not found in DOM');
            return;
        }

        this.extraLinksContainer = container;
        this.extraLinkAddButton = addBtn;
        this.extraLinksInitialized = true;

        this.setupExtraLinkPicker();
        this.renderExtraLinks();
    }

    getExtraLinksFromProfile(profile) {
        if (!profile) {
            return [];
        }

        let extraLinks = profile.extraLinks;

        if (!extraLinks) {
            return [];
        }

        if (typeof extraLinks === 'string') {
            try {
                extraLinks = JSON.parse(extraLinks);
            } catch (error) {
                console.warn('Failed to parse extraLinks JSON from profile', error);
                extraLinks = [];
            }
        }

        if (!Array.isArray(extraLinks)) {
            return [];
        }

        return extraLinks;
    }

    createExtraLinkDataObject(data = {}) {
        const id = typeof data.id === 'string' && data.id.trim() !== ''
            ? data.id
            : this.generateUniqueId('extra');

        const type = data.type === 'file' ? 'file' : 'link';

        return {
            id,
            platform: typeof data.platform === 'string' ? data.platform : '',
            label: typeof data.label === 'string' ? data.label : '',
            type,
            url: typeof data.url === 'string' ? data.url : '',
            storedFilePath: typeof data.storedFilePath === 'string' ? data.storedFilePath : '',
            originalName: typeof data.originalName === 'string' ? data.originalName : '',
            size: typeof data.size === 'number' ? data.size : (data.size ? Number(data.size) : null),
            pendingFile: null,
            element: null
        };
    }

    setExtraLinks(extraLinks) {
        this.extraLinks = Array.isArray(extraLinks)
            ? extraLinks.map((link) => this.createExtraLinkDataObject(link))
            : [];

        if (this.extraLinksContainer) {
            this.renderExtraLinks();
        }
    }

    addExtraLink(initialData = {}) {
        this.ensureExtraLinksInitialized();

        const entry = this.createExtraLinkDataObject(initialData);

        // Авто-метка для дублей: Instagram, Instagram 2 и т.п.
        if (entry.platform) {
            const platforms = this.getSocialPlatformDefinitions();
            const base = platforms[entry.platform];
            const baseName = base ? base.name : entry.platform;
            const duplicates = this.extraLinks.filter(e => e.platform === entry.platform && !e.removed);
            if (duplicates.length >= 1) {
                entry.label = `${baseName} ${duplicates.length + 1}`;
            }
        }

        this.extraLinks.push(entry);

        if (this.extraLinksContainer) {
            if (this.extraLinks.length === 1) {
                this.extraLinksContainer.innerHTML = '';
            }
            const element = this.createExtraLinkElement(entry);
            this.extraLinksContainer.appendChild(element);
            entry.element = element;
        } else {
            this.renderExtraLinks();
        }

        this.updatePreview();
    }

    renderExtraLinks() {
        this.ensureExtraLinksInitialized();

        if (!this.extraLinksContainer) {
            return;
        }

        this.extraLinksContainer.innerHTML = '';

        if (!Array.isArray(this.extraLinks) || this.extraLinks.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'form-help';
            emptyState.style.marginTop = '0.5rem';
            emptyState.textContent = 'Поки що не додано жодного посилання або файлу.';
            this.extraLinksContainer.appendChild(emptyState);
            return;
        }

        this.extraLinks.forEach((entry) => {
            const element = this.createExtraLinkElement(entry);
            this.extraLinksContainer.appendChild(element);
            entry.element = element;
        });
    }

    getExtraLinkPlatformOptions() {
        const platforms = this.getSocialPlatformDefinitions();
        const options = [
            { value: '', label: 'Оберіть платформу' }
        ];

        Object.entries(platforms).forEach(([key, value]) => {
            options.push({ value: key, label: value.name });
        });

        options.push({ value: 'custom', label: 'Інше' });

        return options;
    }

    createExtraLinkElement(entry) {
        const wrapper = document.createElement('div');
        wrapper.className = 'extra-link-item';
        wrapper.dataset.entryId = entry.id;

        const platforms = this.getSocialPlatformDefinitions();
        const base = entry.platform ? platforms[entry.platform] : null;
        const baseName = base ? base.name : (entry.label || 'Посилання');
        const displayName = entry.label || baseName;
        const iconSrc = base ? base.icon : (platforms.site ? platforms.site.icon : 'assets/img/site.png');

        wrapper.innerHTML = `
            <div class="extra-link-card">
                <div class="extra-link-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">
                            <img src="${iconSrc}" alt="${displayName}" class="form-icon">
                            <span>${displayName}</span>
                        </label>
                        <input type="url" class="form-input extra-link-url" placeholder="https://">
                    </div>
                    <div class="form-group extra-link-remove-group" style="flex: 0 0 auto;">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger extra-link-remove">Видалити</button>
                    </div>
                </div>
            </div>
        `;

        // Всегда работаем как "посилання"
        entry.type = 'link';

        const urlInput = wrapper.querySelector('.extra-link-url');
        const removeBtn = wrapper.querySelector('.extra-link-remove');

        urlInput.value = entry.url || '';

        urlInput.addEventListener('input', (event) => {
            entry.url = event.target.value;
            this.updatePreview();
        });

        removeBtn.addEventListener('click', () => {
            const index = this.extraLinks.findIndex((item) => item.id === entry.id);
            if (index !== -1) {
                this.extraLinks.splice(index, 1);
            }

            wrapper.remove();

            if (this.extraLinksContainer && this.extraLinks.length === 0) {
                this.renderExtraLinks();
            }

            this.updatePreview();
        });

        entry.element = wrapper;
        return wrapper;
    }

    updateExtraLinkFileStatus(entry, statusEl) {
        if (!statusEl) {
            return;
        }

        if (entry.pendingFile instanceof File) {
            const sizeText = this.formatFileSize(entry.pendingFile.size);
            const pendingLabel = sizeText
                ? `${entry.pendingFile.name} (${sizeText})`
                : entry.pendingFile.name;
            statusEl.textContent = `Обрано файл: ${pendingLabel}. Збережіть зміни, щоб прикріпити.`;
            return;
        }

        if (entry.storedFilePath) {
            const href = this.formatFileUrl(entry.storedFilePath);
            const displayName = entry.originalName || this.getFileDisplayName(entry.storedFilePath);
            statusEl.innerHTML = `Поточний файл: <a href="${href}" target="_blank" rel="noopener noreferrer" download="${displayName}">${displayName}</a>`;
            return;
        }

        statusEl.textContent = '';
    }

    generateUniqueId(prefix = 'id') {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return `${prefix}-${crypto.randomUUID()}`;
        }
        const randomPart = Math.random().toString(16).slice(2, 10);
        return `${prefix}-${Date.now()}-${randomPart}`;
    }

    buildExtraLinksPayload(formData) {
        if (!Array.isArray(this.extraLinks) || this.extraLinks.length === 0) {
            return { payload: [], hasFileUploads: false };
        }

        const payload = [];
        let extraFileIndex = 0;
        let validationFailed = false;
        let missingFileWarningShown = false;
        let hasFileUploads = false;

        this.extraLinks.forEach((entry) => {
            if (!entry) {
                return;
            }

            const sanitized = {
                id: entry.id || this.generateUniqueId('extra'),
                platform: entry.platform || '',
                label: (entry.label || '').trim(),
                type: entry.type === 'file' ? 'file' : 'link',
                url: '',
                storedFilePath: '',
                originalName: '',
                size: null
            };

            if (sanitized.type === 'link') {
                const value = entry.url ? entry.url.trim() : '';
                if (!value) {
                    return;
                }
                sanitized.url = this.normalizeUrl(value, sanitized.platform);
            } else {
                const pendingFile = entry.pendingFile instanceof File ? entry.pendingFile : null;
                const hasStoredFile = entry.storedFilePath && entry.storedFilePath.trim() !== '';

                sanitized.storedFilePath = hasStoredFile ? entry.storedFilePath : '';

                if (pendingFile) {
                    if (pendingFile.size > 50 * 1024 * 1024) {
                        Utils.showNotification(`Файл "${pendingFile.name}" занадто великий (максимум 50MB)`, 'error');
                        validationFailed = true;
                        return;
                    }

                    const fieldName = `extraFile_${extraFileIndex++}`;
                    sanitized.fileField = fieldName;
                    formData.append(fieldName, pendingFile, pendingFile.name);
                    sanitized.originalName = pendingFile.name;
                    sanitized.size = pendingFile.size;
                    hasFileUploads = true;
                } else if (hasStoredFile) {
                    sanitized.originalName = entry.originalName || this.getFileDisplayName(entry.storedFilePath);
                    sanitized.size = entry.size ? Number(entry.size) : null;
                } else {
                    if (!missingFileWarningShown) {
                        Utils.showNotification('Для додаткового запису оберіть файл або змініть тип на посилання.', 'warning');
                        missingFileWarningShown = true;
                    }
                    validationFailed = true;
                    return;
                }
            }

            sanitized.order = payload.length;
            payload.push(sanitized);
        });

        if (validationFailed) {
            return null;
        }

        return { payload, hasFileUploads };
    }

    normalizeUrl(url, fieldName = '') {
        if (!url) return '';
        
        // Fields that should not be treated as URLs (banks, crypto wallets)
        const nonUrlFields = ['privatBank', 'monoBank', 'alfaBank', 'abank', 'pumbBank', 'raiffeisenBank', 'senseBank', 'binance', 'trustWallet'];
        
        // If it's a bank or crypto field, return as-is (might be card number or wallet address)
        if (nonUrlFields.includes(fieldName)) {
            return url;
        }
        
        // Add protocol if missing for URL fields
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            return 'https://' + url;
        }
        
        return url;
    }

    hexToRgba(hex, opacity) {
        // Validate input
        if (!hex || typeof hex !== 'string') {
            console.error('Invalid hex color:', hex);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Remove # if present
        hex = hex.replace('#', '');
        
        // Validate hex length
        if (hex.length !== 6) {
            console.error('Invalid hex color length:', hex);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Convert hex to RGB
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        // Validate RGB values
        if (isNaN(r) || isNaN(g) || isNaN(b)) {
            console.error('Invalid RGB values:', r, g, b);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

    formatImageSource(value) {
        if (!value || typeof value !== 'string') {
            return null;
        }

        const trimmed = value.trim();
        if (trimmed === '') {
            return null;
        }

        const normalized = trimmed.replace(/\\/g, '/');

        if (normalized.startsWith('data:')) {
            return normalized;
        }

        if (normalized.startsWith('http://') || normalized.startsWith('https://')) {
            return normalized;
        }

        if (normalized.startsWith('/uploads/')) {
            return normalized;
        }

        if (normalized.startsWith('uploads/')) {
            return `/${normalized}`;
        }

        return `data:image/*;base64,${normalized}`;
    }

    resolveCompanyBranding() {
        const branding = {
            name: '',
            tagline: '',
            logo: null,
            showLogo: false,
            showName: false
        };

        if (this.companyData) {
            branding.name = this.companyData.company_name || branding.name;
        }

        if (this.companyData && this.companyData.design) {
            const design = this.companyData.design;
            if (design.display_name) {
                branding.name = design.display_name;
            }
            if (design.tagline) {
                branding.tagline = design.tagline;
            }
            if (design.avatar) {
                branding.logo = this.formatImageSource(design.avatar);
            }
            if (design.show_logo !== undefined && design.show_logo !== null) {
                branding.showLogo = Boolean(Number(design.show_logo));
            }
            if (design.show_name !== undefined && design.show_name !== null) {
                branding.showName = Boolean(Number(design.show_name));
            }
        }

        if (this.profileData) {
            if (this.profileData.companyDisplayName) {
                branding.name = this.profileData.companyDisplayName;
            }
            if (this.profileData.companyTagline) {
                branding.tagline = this.profileData.companyTagline;
            }
            if (this.profileData.companyLogo) {
                branding.logo = this.formatImageSource(this.profileData.companyLogo);
            }
            if (this.profileData.companyShowLogo !== undefined && this.profileData.companyShowLogo !== null) {
                branding.showLogo = Number(this.profileData.companyShowLogo) === 1;
            }
            if (this.profileData.companyShowName !== undefined && this.profileData.companyShowName !== null) {
                branding.showName = Number(this.profileData.companyShowName) === 1;
            }
            if (!branding.name && this.profileData.companyName) {
                branding.name = this.profileData.companyName;
            }
        }

        if (!branding.name && this.companyData && this.companyData.company_name) {
            branding.name = this.companyData.company_name;
        }

        if (!branding.showName && branding.name) {
            branding.showName = true;
        }

        return branding;
    }

    handleFilePreview(event) {
        const inputId = event.target.id;
        const file = event.target.files[0];

        if (inputId === 'fileUploadInput') {
            if (file) {
                this.uploadedFiles.file = file;
                this.pendingFileAttachment = {
                    name: file.name,
                    size: file.size
                };
                const fileLinkInput = document.getElementById('fileUpload');
                if (fileLinkInput) {
                    fileLinkInput.value = '';
                }
            } else {
                this.uploadedFiles.file = null;
                this.pendingFileAttachment = null;
            }
            this.updateFileUploadStatus();
            this.updatePreview();
            return;
        }

        if (inputId === 'customLogo') {
            if (!file) {
                this.uploadedFiles.customLogo = null;
                this.updateCustomLogoStatus();
                this.updateCustomLogoPreview();
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                Utils.showNotification(`Логотип занадто великий (${this.formatFileSize(file.size)}). Максимальний розмір 10MB.`, 'error');
                event.target.value = '';
                this.uploadedFiles.customLogo = null;
                this.updateCustomLogoStatus();
                this.updateCustomLogoPreview();
                return;
            }
        }

        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const fileDataUrl = e.target.result;
            
            if (inputId === 'avatar') {
                // Store in uploadedFiles
                this.uploadedFiles.avatar = fileDataUrl;
                
                // Update preview immediately
                const previewAvatarImg = document.getElementById('previewAvatarImg');
                if (previewAvatarImg) {
                    previewAvatarImg.src = fileDataUrl;
                    previewAvatarImg.style.display = 'block';
                }
                
                // Update profileData if exists
                if (this.profileData) {
                    // Extract base64 from data URL
                    const base64 = fileDataUrl.split(',')[1];
                    this.profileData.avatar = base64;
                }
            } else if (inputId === 'background') {
                // Store in uploadedFiles
                this.uploadedFiles.background = fileDataUrl;
                
                // Update preview immediately
                const container = document.getElementById('profilePreviewContainer');
                if (container) {
                    container.style.setProperty('background-image', `url(${fileDataUrl})`, 'important');
                    container.style.setProperty('background-size', 'cover', 'important');
                    container.style.setProperty('background-position', 'center', 'important');
                    container.style.setProperty('background-repeat', 'no-repeat', 'important');
                    container.classList.add('has-background-image');
                }
                
                // Update profileData if exists
                if (this.profileData) {
                    const base64 = fileDataUrl.split(',')[1];
                    this.profileData.background = base64;
                    this.profileData.bg = base64;
                }
            } else if (inputId === 'blockImage' || inputId === 'blockImage2') {
                // Store in uploadedFiles
                this.uploadedFiles.blockImage = fileDataUrl;
                
                // Update preview immediately
                const card = document.getElementById('profilePreviewCard');
                if (card) {
                    card.style.setProperty('background-image', `url(${fileDataUrl})`, 'important');
                    card.style.setProperty('background-size', 'cover', 'important');
                    card.style.setProperty('background-position', 'center', 'important');
                    card.style.setProperty('background-repeat', 'no-repeat', 'important');
                }
                
                // Update profileData if exists
                if (this.profileData) {
                    const base64 = fileDataUrl.split(',')[1];
                    this.profileData.blockImage = base64;
                    this.profileData.block_image = base64;
                }
            } else if (inputId === 'socialBgImage') {
                // Store in uploadedFiles
                this.uploadedFiles.socialBgImage = fileDataUrl;
                
                // Update preview immediately
                const previewLinks = document.getElementById('previewLinks');
                if (previewLinks) {
                    previewLinks.style.setProperty('background-image', `url(${fileDataUrl})`, 'important');
                    previewLinks.style.setProperty('background-size', 'cover', 'important');
                    previewLinks.style.setProperty('background-position', 'center', 'important');
                    previewLinks.style.setProperty('background-repeat', 'no-repeat', 'important');
                    previewLinks.classList.add('has-background-image');
                }
                
                // Update profileData if exists
                if (this.profileData) {
                    const base64 = fileDataUrl.split(',')[1];
                    this.profileData.socialBgImage = base64;
                }
            } else if (inputId === 'customLogo') {
                this.uploadedFiles.customLogo = fileDataUrl;
                this.updateCustomLogoStatus();
                this.updateCustomLogoPreview();
                return;
            }
            
            // Don't show notification here - it will be shown after successful save
            // Just update the preview silently
        };
        
        reader.onerror = () => {
            Utils.showNotification('Помилка завантаження зображення', 'error');
        };
        
        reader.readAsDataURL(file);
    }

    updateCharacterCounter() {
        const description = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (description && charCount) {
            const length = description.value.length;
            charCount.textContent = length;
            
            if (length > 450) {
                charCount.style.color = 'var(--danger-color)';
            } else if (length > 400) {
                charCount.style.color = 'var(--warning-color)';
            } else {
                charCount.style.color = 'var(--text-muted)';
            }
        }
    }

    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('Form submitted');
        
        try {
            Utils.showLoading('Збереження профілю...');
            
            const formData = this.createFormData();
            if (!formData) {
                console.warn('Form submission cancelled due to validation errors.');
                return;
            }
            
            // Log file uploads
            const fileInputs = ['avatar', 'background', 'blockImage', 'blockImage2', 'socialBgImage', 'fileUploadInput'];
            fileInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input && input.files[0]) {
                    console.log(`File to upload: ${id}, size: ${input.files[0].size} bytes, type: ${input.files[0].type}`);
                }
            });
            
            const response = await fetch('api/update-profile.php', {
                method: 'POST',
                body: formData
                // Don't set Content-Type header - browser will set it automatically with boundary for FormData
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', response.status, errorText);
                
                // Try to parse error message from JSON
                let errorMessage = 'Помилка сервера: ' + response.status;
                try {
                    const errorData = JSON.parse(errorText);
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    } else if (errorData.error) {
                        errorMessage = errorData.error;
                    }
                } catch (e) {
                    // If not JSON, use the text as is (but limit length)
                    if (errorText && errorText.length < 200) {
                        errorMessage = errorText;
                    }
                }
                
                Utils.showNotification(errorMessage, 'error');
                return;
            }

            let data;
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            // Try to extract JSON from mixed HTML/JSON response
            let cleanResponse = responseText;
            const jsonMatch = responseText.match(/\{.*\}/s);
            if (jsonMatch) {
                cleanResponse = jsonMatch[0];
                console.log('Extracted JSON:', cleanResponse);
            }
            
            try {
                data = JSON.parse(cleanResponse);
                console.log('Update response:', data);
            } catch (jsonError) {
                console.error('JSON parse error:', jsonError);
                console.error('Response text:', responseText);
                console.error('Clean response:', cleanResponse);
                Utils.showNotification('Помилка: сервер повернув некоректну відповідь', 'error');
                return;
            }

            if (data.success) {
                Utils.showNotification('Профіль успішно оновлено!', 'success');
                
                console.log('Response profile data:', data.profile);
                console.log('Avatar present:', !!data.profile.avatar);
                console.log('Background present:', !!data.profile.background);
                console.log('BlockImage present:', !!data.profile.blockImage);
                
                this.profileData = data.profile;
                console.log('Updated profileData:', this.profileData);
                
                // Clear uploadedFiles since they're now saved
                this.uploadedFiles = {
                    avatar: null,
                    background: null,
                    blockImage: null,
                    socialBgImage: null,
                    customLogo: null,
                    file: null
                };
                this.pendingFileAttachment = null;
                
                // Clear file inputs
                const fileInputs = ['avatar', 'background', 'blockImage', 'blockImage2', 'socialBgImage', 'customLogo', 'fileUploadInput'];
                fileInputs.forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.value = '';
                    }
                });

                this.updateFileUploadStatus();
                this.updateCustomLogoStatus();
                
                // Save lightweight profile to localStorage for persistence
                this.cacheProfileLocally(this.profileData);
                
                // Force update form and preview with new data
                this.populateForm();
                setTimeout(() => {
                    this.updatePreview();
                    // Update profile link and QR code after profile update
                    this.setupProfileLink();
                }, 200);
                
            } else {
                console.error('Error updating profile:', data.message);
                Utils.showNotification(data.message || 'Помилка оновлення профілю', 'error');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            Utils.showNotification('Помилка з\'єднання. Спробуйте ще раз.', 'error');
        } finally {
            Utils.hideLoading();
        }
    }

    createFormData() {
        const formData = new FormData();
        
        // Add userID, username and password for authentication
        formData.append('userID', localStorage.getItem('userID'));
        formData.append('username', localStorage.getItem('username'));
        formData.append('password', localStorage.getItem('password'));
        
        // Debug: Log all form inputs before creating FormData
        console.log('=== FORM INPUTS DEBUG ===');
        const colorInputs = ['profileColor', 'textColor', 'socialBgColor', 'socialTextColor'];
        colorInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                console.log(`${id}: "${input.value}" (type: ${input.type}, length: ${input.value.length})`);
                // Check if the value looks correct
                if (input.value.length !== 7 || !input.value.startsWith('#')) {
                    console.warn(`WARNING: ${id} has unexpected format: "${input.value}"`);
                }
            } else {
                console.log(`${id}: NOT FOUND`);
            }
        });
        console.log('=== END FORM INPUTS DEBUG ===');
        
        // Add form fields
        const fields = [
            'description', 'companyPosition',
            'profileColor', 'textColor', 'textBgColor', 'profileOpacity', 'textOpacity', 'textBgOpacity',
            'socialBgColor', 'socialTextColor', 'socialOpacity',
            'instagram', 'youtube', 'youtubeMusic', 'tiktok', 'facebook', 'x', 'linkedin',
            'twitch', 'steam', 'discord', 'telegram',
            'spotify', 'soundcloud',
            'github', 'site',
            'googleDocs', 'googleSheets', 'fileUpload',
            'upwork', 'fiverr', 'djinni',
            'reddit', 'whatsapp', 'viber',
            'dou', 'olx', 'amazon', 'prom', 'fhunt', 'dj',
            'privatBank', 'monoBank', 'alfaBank', 'abank', 'pumbBank', 'raiffeisenBank', 'senseBank',
            'binance', 'trustWallet',
            'customLogoPosition', 'customLogoSize'
        ];
        
        fields.forEach(field => {
            // Special handling for textBgColor - only send if checkbox is enabled
            if (field === 'textBgColor') {
                const enableTextBgCheckbox = document.getElementById('enableTextBg');
                const textBgColorInput = document.getElementById('textBgColor');
                if (enableTextBgCheckbox && textBgColorInput) {
                    const value = enableTextBgCheckbox.checked && textBgColorInput.value ? textBgColorInput.value : '';
                    formData.append(field, value);
                    console.log(`Sending ${field}: ${value} (enabled: ${enableTextBgCheckbox.checked})`);
                }
            } else {
                const input = document.getElementById(field);
                if (input) {
                    if (field === 'companyPosition') {
                        const positionValue = input.value ? input.value.trim() : '';
                        formData.append(field, positionValue);
                        return;
                    }
                    const value = input.value || '';
                    formData.append(field, value);
                    // Log all color-related fields
                    if (field === 'profileColor' || field === 'textColor' || 
                        field === 'profileOpacity' || field === 'textOpacity' || field === 'textBgOpacity' ||
                        field === 'socialBgColor' || field === 'socialTextColor' || 
                        field === 'socialOpacity') {
                        console.log(`Sending ${field}: ${value}`);
                    }
                }
            }
        });
        
        // Add file uploads
        const fileFields = ['avatar', 'background', 'blockImage', 'blockImage2', 'socialBgImage', 'customLogo', 'fileUploadInput'];
        let hasFiles = false;
        fileFields.forEach(field => {
            const input = document.getElementById(field);
            if (input && input.files && input.files.length > 0 && input.files[0]) {
                const file = input.files[0];
                // For blockImage and blockImage2, use 'blockImage' as the form field name
                const formFieldName = (field === 'blockImage' || field === 'blockImage2') ? 'blockImage' : field;
                
                // Validate file size before adding
                if (file.size > 50 * 1024 * 1024) {
                    console.error(`File ${field} is too large: ${file.size} bytes (max 50MB)`);
                    Utils.showNotification(`Файл ${field} занадто великий (максимум 50MB)`, 'error');
                    return;
                }
                
                formData.append(formFieldName, file, file.name);
                console.log(`Sending file: ${field} as ${formFieldName}, name: ${file.name}, size: ${file.size} bytes, type: ${file.type}`);
                hasFiles = true;
            } else {
                console.log(`No file for ${field} (input exists: ${!!input}, files length: ${input && input.files ? input.files.length : 0})`);
            }
        });
        
        if (!hasFiles) {
            console.log('No files to upload');
        } else {
            console.log(`Total files to upload: ${fileFields.filter(f => {
                const input = document.getElementById(f);
                return input && input.files && input.files.length > 0;
            }).length}`);
        }
        
        const extraLinksResult = this.buildExtraLinksPayload(formData);
        if (extraLinksResult === null) {
            return null;
        }

        if (extraLinksResult.hasFileUploads) {
            hasFiles = true;
        }

        formData.append('extraLinks', JSON.stringify(extraLinksResult.payload));
        
        // Log FormData contents (can't directly inspect, but we can log what we added)
        console.log('FormData created with files:', hasFiles);
        console.log('Extra links payload:', extraLinksResult.payload);
        
        return formData;
    }

    getProfileCachePayload(profile) {
        if (!profile || typeof profile !== 'object') {
            return null;
        }

        const {
            avatar,
            bg,
            background,
            blockImage,
            block_image,
            socialBgImage,
            socialBg_image,
            customLogo,
            ...rest
        } = profile;

        return { ...rest };
    }

    cacheProfileLocally(profile) {
        if (!profile) {
            try {
                localStorage.removeItem('userProfile');
            } catch (error) {
                console.warn('Failed to clear cached profile', error);
            }
            return;
        }

        const cachePayload = this.getProfileCachePayload(profile);
        if (!cachePayload) {
            return;
        }

        try {
            localStorage.setItem('userProfile', JSON.stringify(cachePayload));
        } catch (error) {
            console.warn('Failed to cache profile in localStorage', error);
            try {
                localStorage.removeItem('userProfile');
            } catch (clearError) {
                console.warn('Failed to clear cached profile after quota issue', clearError);
            }
        }
    }

    setupProfileLink() {
        console.log('Setting up profile link...');
        
        if (!this.profileData || !this.profileData.username) {
            console.log('No profile data or username for link setup');
            return;
        }

        const linkInput = document.getElementById('profileLink');
        if (linkInput) {
            // Use clean URL format: bionrgg.com/username
            const hostname = window.location.hostname;
            // Remove port if present (for localhost development)
            const cleanHostname = hostname.split(':')[0];
            // For display, show without protocol, but for QR code use full URL
            const displayUrl = `${cleanHostname}/${this.profileData.username}`;
            // Ensure we have a proper protocol (default to https for production, http for localhost)
            const protocol = window.location.protocol || (cleanHostname === 'localhost' || cleanHostname === '127.0.0.1' ? 'http:' : 'https:');
            const fullUrl = `${protocol}//${cleanHostname}/${this.profileData.username}`;
            
            linkInput.value = displayUrl;
            console.log('Profile link set:', displayUrl);
            console.log('Full URL for QR code:', fullUrl);
            
            // Generate QR code with delay to ensure DOM is ready (use full URL for QR)
            setTimeout(() => {
                this.generateQRCode(fullUrl);
            }, 500);
        } else {
            console.error('Profile link input not found');
        }
    }

    generateQRCode(url) {
        console.log('Generating QR code for URL:', url);
        
        // Validate URL
        if (!url || typeof url !== 'string') {
            console.error('Invalid URL for QR code:', url);
            return;
        }
        
        // Ensure URL is absolute
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            console.warn('URL is not absolute, making it absolute:', url);
            const protocol = window.location.protocol || 'https:';
            const hostname = window.location.hostname || 'bionrgg.com';
            url = `${protocol}//${hostname}${url.startsWith('/') ? url : '/' + url}`;
            console.log('Corrected URL:', url);
        }
        
        const qrDisplay = document.getElementById('qrCodeDisplay');
        const downloadBtn = document.getElementById('downloadQRBtn');
        
        if (!qrDisplay) {
            console.error('QR code display element not found');
            return;
        }

        // Function to generate QR code using API as fallback
        const generateQRViaAPI = () => {
            console.log('Using API to generate QR code for:', url);
            const encodedUrl = encodeURIComponent(url);
            const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodedUrl}`;
            
            const imgElement = document.createElement('img');
            imgElement.src = apiUrl;
            imgElement.style.width = '200px';
            imgElement.style.height = '200px';
            imgElement.alt = 'QR Code';
            imgElement.style.display = 'block';
            imgElement.title = url;
            imgElement.onerror = () => {
                qrDisplay.innerHTML = '<p style="color: var(--danger-color); text-align: center;">Помилка генерації QR коду через API</p>';
            };
            
            qrDisplay.innerHTML = '';
            qrDisplay.appendChild(imgElement);
            
            // Show download button
            if (downloadBtn) {
                downloadBtn.style.display = 'inline-flex';
                downloadBtn.onclick = () => {
                    const link = document.createElement('a');
                    link.download = `qr-code-${this.profileData.username}.png`;
                    link.href = apiUrl;
                    link.click();
                };
            }
        };

        // Function to actually generate QR code
        const generateQR = () => {
            console.log('QRCode library is available, generating QR code for:', url);
            
            // Clear previous QR code
            qrDisplay.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Генерація QR коду...</p>';
            
            // Generate QR code using toDataURL (more reliable)
            try {
                const QRCodeLib = window.QRCode || QRCode;
                if (!QRCodeLib || !QRCodeLib.toDataURL) {
                    throw new Error('QRCode library not properly loaded');
                }
                
                QRCodeLib.toDataURL(url, {
                    width: 200,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    },
                    errorCorrectionLevel: 'M'
                }, (error, imgData) => {
                    if (error) {
                        console.error('Error generating QR code:', error);
                        console.log('Falling back to API method');
                        generateQRViaAPI();
                        return;
                    }
                    
                    console.log('QR code generated successfully for URL:', url);
                    
                    // Display QR code
                    const imgElement = document.createElement('img');
                    imgElement.src = imgData;
                    imgElement.style.width = '200px';
                    imgElement.style.height = '200px';
                    imgElement.alt = 'QR Code';
                    imgElement.style.display = 'block';
                    imgElement.title = url; // Add URL as title for debugging
                    qrDisplay.innerHTML = '';
                    qrDisplay.appendChild(imgElement);
                    
                    // Show download button
                    if (downloadBtn) {
                        downloadBtn.style.display = 'inline-flex';
                        downloadBtn.onclick = () => {
                            const link = document.createElement('a');
                            link.download = `qr-code-${this.profileData.username}.png`;
                            link.href = imgData;
                            link.click();
                        };
                    }
                });
            } catch (e) {
                console.error('Exception generating QR code:', e);
                console.log('Falling back to API method');
                generateQRViaAPI();
            }
        };

        // Check if QRCode library is loaded - wait for it with multiple attempts
        let attempts = 0;
        const maxAttempts = 10;
        
        const checkQRCode = () => {
            attempts++;
            
            // Try multiple ways to access QRCode
            let QRCodeLib = null;
            if (typeof window !== 'undefined') {
                QRCodeLib = window.QRCode || window.qrcode || window.QRCodeJS;
            }
            if (!QRCodeLib && typeof QRCode !== 'undefined') {
                QRCodeLib = QRCode;
            }
            
            if (QRCodeLib && typeof QRCodeLib.toDataURL === 'function') {
                console.log('QRCode library found, generating QR code');
                generateQR();
                return;
            }
            
            if (attempts < maxAttempts) {
                console.log(`QRCode library check attempt ${attempts}/${maxAttempts}...`);
                qrDisplay.innerHTML = `<p style="color: var(--text-muted); text-align: center;">Завантаження бібліотеки QR коду... (${attempts}/${maxAttempts})</p>`;
                
                setTimeout(() => {
                    checkQRCode();
                }, 500);
            } else {
                // All attempts failed, try to load from alternative sources
                console.log('QRCode library not found after attempts, trying alternative CDN sources...');
                
                const cdnSources = [
                    'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js',
                    'https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'
                ];
                
                let currentSourceIndex = 0;
                let scriptLoaded = false;
                
                const tryLoadLibrary = () => {
                    if (scriptLoaded) return;
                    
                    if (currentSourceIndex >= cdnSources.length) {
                        console.error('All CDN sources failed, using API fallback');
                        qrDisplay.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Завантаження QR коду через API...</p>';
                        // Use API as fallback
                        generateQRViaAPI();
                        return;
                    }
                    
                    // Check if script already exists
                    const existingScript = document.querySelector(`script[src="${cdnSources[currentSourceIndex]}"]`);
                    if (existingScript) {
                        console.log('Script already exists, checking library...');
                        setTimeout(() => {
                            const QRCodeLibCheck = window.QRCode || (typeof QRCode !== 'undefined' ? QRCode : null);
                            if (QRCodeLibCheck && QRCodeLibCheck.toDataURL) {
                                scriptLoaded = true;
                                generateQR();
                            } else {
                                currentSourceIndex++;
                                tryLoadLibrary();
                            }
                        }, 200);
                        return;
                    }
                    
                    const script = document.createElement('script');
                    script.src = cdnSources[currentSourceIndex];
                    script.async = true;
                    script.crossOrigin = 'anonymous';
                    
                    script.onload = () => {
                        console.log('QRCode library loaded from:', cdnSources[currentSourceIndex]);
                        scriptLoaded = true;
                        setTimeout(() => {
                            const QRCodeLibLoaded = window.QRCode || (typeof QRCode !== 'undefined' ? QRCode : null);
                            if (QRCodeLibLoaded && QRCodeLibLoaded.toDataURL) {
                                generateQR();
                            } else {
                                console.warn('Library loaded but QRCode.toDataURL not available');
                                currentSourceIndex++;
                                scriptLoaded = false;
                                tryLoadLibrary();
                            }
                        }, 200);
                    };
                    
                    script.onerror = () => {
                        console.warn('Failed to load QRCode library from:', cdnSources[currentSourceIndex]);
                        currentSourceIndex++;
                        tryLoadLibrary();
                    };
                    
                    document.head.appendChild(script);
                };
                
                tryLoadLibrary();
            }
        };
        
        // Start checking immediately
        checkQRCode();
    }

    copyProfileLink() {
        const linkInput = document.getElementById('profileLink');
        if (!linkInput || !linkInput.value) {
            console.log('Profile link not available');
            return;
        }

        Utils.copyToClipboard(linkInput.value)
            .then(() => {
                Utils.showNotification('Profile link copied to clipboard!', 'success');
            })
            .catch(() => {
                // Don't show error to user
                console.log('Failed to copy link');
            });
    }

    async deleteProfile() {
        const confirmed = confirm('Are you sure you want to delete your profile? This action cannot be undone.');
        if (!confirmed) return;

        try {
            Utils.showLoading('Deleting profile...');
            
            const response = await fetch('api/delete-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: localStorage.getItem('username') || localStorage.getItem('userID'),
                    userID: localStorage.getItem('userID'),
                    password: localStorage.getItem('password')
                })
            });

            const data = await response.json();
            console.log('Delete response:', data);

            if (data.success) {
                Utils.showNotification('Profile deleted successfully!', 'success');
                localStorage.removeItem('userID');
                localStorage.removeItem('password');
                localStorage.removeItem('username');
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 2000);
            } else {
                console.error('Error deleting profile:', data.message);
                // Don't show error to user
                console.log('Operation failed');
            }
        } catch (error) {
            console.error('Error deleting profile:', error);
            Utils.showNotification('Спробуйте ще раз', 'info');
        } finally {
            Utils.hideLoading();
        }
    }

    openEditor() {
        const editorSection = document.getElementById('profileEditorSection');
        const editBtn = document.getElementById('editProfileBtn');
        
        if (!editorSection) return;
        
        // Show editor with animation
        editorSection.style.display = 'block';
        editorSection.style.opacity = '0';
        editorSection.style.transform = 'translateY(-20px)';
        editorSection.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        
        // Trigger animation
        setTimeout(() => {
            editorSection.style.opacity = '1';
            editorSection.style.transform = 'translateY(0)';
        }, 10);
        
        // Scroll to editor
        setTimeout(() => {
            editorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
        
        // Hide edit button
        if (editBtn) {
            editBtn.style.display = 'none';
        }
    }

    closeEditor() {
        const editorSection = document.getElementById('profileEditorSection');
        const editBtn = document.getElementById('editProfileBtn');
        
        if (!editorSection) return;
        
        // Animate out
        editorSection.style.opacity = '0';
        editorSection.style.transform = 'translateY(-20px)';
        
        // Hide after animation
        setTimeout(() => {
            editorSection.style.display = 'none';
        }, 400);
        
        // Show edit button
        if (editBtn) {
            editBtn.style.display = 'inline-flex';
        }
    }

    setupCompanyDesignModal() {
        // Unified company design has been removed; modal setup is no longer required.
    }

    openCompanyDesignModal() {
        // Unified company design has been removed; modal interactions are disabled.
    }

    closeCompanyDesignModal() {
        // Unified company design has been removed; modal interactions are disabled.
    }

    populateCompanyDesignForm() {
        // Unified company design has been removed; nothing to populate.
    }

    setActiveCompanyDesignToggle(target, type) {
        // Unified company design has been removed; toggles are no longer used.
    }

    updateCompanyDesignBgGroups() {
        // Unified company design has been removed; background groups are handled per profile.
    }

    updateCompanyDesignSocialBgGroups() {
        // Unified company design has been removed; background groups are handled per profile.
    }

    updateCompanyDesignPreview() {
        // Unified company design has been removed; preview rendering is not needed.
    }

    async handleCompanyDesignSubmit(event) {
        event.preventDefault();
        Utils.showNotification('Єдиний дизайн компанії вимкнено. Кожен профіль має власні налаштування.', 'info');
    }

    setupToggleButtons() {
        // Profile background toggle
        const profileToggleBtns = document.querySelectorAll('[data-target="profileBgType"]');
        profileToggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                profileToggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const isColor = btn.dataset.type === 'color';
                const colorGroup = document.getElementById('profileBgColorGroup');
                const imageGroup = document.getElementById('profileBgImageGroup');
                
                if (colorGroup && imageGroup) {
                    colorGroup.style.display = isColor ? 'block' : 'none';
                    imageGroup.style.display = isColor ? 'none' : 'block';
                }
            });
        });
        
        // Auto-switch to color mode when profileColor changes
        const profileColorInput = document.getElementById('profileColor');
        if (profileColorInput) {
            profileColorInput.addEventListener('change', () => {
                // Switch to color mode
                const colorBtn = document.querySelector('[data-target="profileBgType"][data-type="color"]');
                if (colorBtn) {
                    colorBtn.click();
                }
                
                // Clear blockImage file input
                const blockImageInput = document.getElementById('blockImage');
                const blockImageInput2 = document.getElementById('blockImage2');
                if (blockImageInput) blockImageInput.value = '';
                if (blockImageInput2) blockImageInput2.value = '';
            });
        }
        
        // Auto-switch to image mode when blockImage is selected
        const blockImageInput = document.getElementById('blockImage');
        const blockImageInput2 = document.getElementById('blockImage2');
        
        const switchToImageMode = () => {
            // Switch to image mode
            const imageBtn = document.querySelector('[data-target="profileBgType"][data-type="image"]');
            if (imageBtn) {
                imageBtn.click();
            }
        };
        
        if (blockImageInput) {
            blockImageInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    switchToImageMode();
                }
            });
        }
        
        if (blockImageInput2) {
            blockImageInput2.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    switchToImageMode();
                }
            });
        }

        // Social background toggle
        const socialToggleBtns = document.querySelectorAll('[data-target="socialBgType"]');
        socialToggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                socialToggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const isColor = btn.dataset.type === 'color';
                const colorGroup = document.getElementById('socialBgColorGroup');
                const imageGroup = document.getElementById('socialBgImageGroup');
                
                if (colorGroup && imageGroup) {
                    colorGroup.style.display = isColor ? 'block' : 'none';
                    imageGroup.style.display = isColor ? 'none' : 'block';
                }
            });
        });
        
        // Auto-switch to color mode when socialBgColor changes
        const socialBgColorInput = document.getElementById('socialBgColor');
        if (socialBgColorInput) {
            socialBgColorInput.addEventListener('change', () => {
                // Switch to color mode
                const colorBtn = document.querySelector('[data-target="socialBgType"][data-type="color"]');
                if (colorBtn) {
                    colorBtn.click();
                }
                
                // Clear socialBgImage file input
                const socialBgImageInput = document.getElementById('socialBgImage');
                if (socialBgImageInput) socialBgImageInput.value = '';
            });
        }
        
        // Auto-switch to image mode when socialBgImage is selected
        const socialBgImageInput = document.getElementById('socialBgImage');
        if (socialBgImageInput) {
            socialBgImageInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    // Switch to image mode
                    const imageBtn = document.querySelector('[data-target="socialBgType"][data-type="image"]');
                    if (imageBtn) {
                        imageBtn.click();
                    }
                }
            });
        }

        // Social opacity slider
        const socialOpacitySlider = document.getElementById('socialOpacity');
        const socialOpacityValue = document.getElementById('socialOpacityValue');
        if (socialOpacitySlider && socialOpacityValue) {
            socialOpacitySlider.addEventListener('input', (e) => {
                let value = parseInt(e.target.value);
                if (value < 2) {
                    value = 2;
                    e.target.value = 2;
                }
                socialOpacityValue.textContent = value + '%';
                this.updatePreview();
            });
        }

        // Text background opacity slider
        const textBgOpacitySlider = document.getElementById('textBgOpacity');
        const textBgOpacityValue = document.getElementById('textBgOpacityValue');
        if (textBgOpacitySlider && textBgOpacityValue) {
            textBgOpacitySlider.addEventListener('input', (e) => {
                let value = parseInt(e.target.value);
                if (value < 2) {
                    value = 2;
                    e.target.value = 2;
                }
                textBgOpacityValue.textContent = value + '%';
                this.updatePreview();
            });
        }
    }

}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing MyProfilePage...');
    window.myProfile = new MyProfilePage();
});

// Make functions globally accessible
window.copyProfileLink = () => {
    if (window.myProfile) {
        window.myProfile.copyProfileLink();
    }
};

window.deleteProfile = () => {
    if (window.myProfile) {
        window.myProfile.deleteProfile();
    }
};
window.copyCompanyKey = async (key) => {
    if (!key) {
        Utils.showNotification('Ключ не знайдено', 'error');
        return;
    }
    
    try {
        // Try modern clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(key);
            Utils.showNotification('Ключ скопійовано!', 'success');
            return;
        }
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = key;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
                Utils.showNotification('Ключ скопійовано!', 'success');
            } else {
                throw new Error('Copy command failed');
            }
        } catch (err) {
            document.body.removeChild(textArea);
            throw err;
        }
    } catch (error) {
        console.error('Failed to copy:', error);
        Utils.showNotification('Не вдалося скопіювати ключ. Скопіюйте вручну: ' + key, 'error');
    }
};

window.MyProfilePage = MyProfilePage;
window.MyProfilePage = MyProfilePage;