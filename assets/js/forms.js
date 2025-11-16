// Utility functions
class Utils {
    static showNotification(message, type = 'info') {
        // Remove existing notifications to prevent stacking
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => {
            n.style.transform = 'translateX(100%)';
            setTimeout(() => n.remove(), 300);
        });
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Icons for different notification types
        const icons = {
            success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <path d="M20 6L9 17l-5-5"/>
            </svg>`,
            error: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>`,
            warning: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>`,
            info: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>`
        };
        
        // Set background color based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#2573c1'
        };
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    ${icons[type] || icons.info}
                </div>
                <div style="flex: 1; line-height: 1.5;">
                    ${message}
                </div>
            </div>
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            z-index: 10000;
            max-width: 400px;
            min-width: 300px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s ease;
            opacity: 0;
            backdrop-filter: blur(10px);
        `;
        
        notification.style.backgroundColor = colors[type] || colors.info;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                notification.style.transform = 'translateX(0)';
                notification.style.opacity = '1';
            });
        });
        
        // Remove after 4 seconds (longer for better UX)
        setTimeout(() => {
            notification.style.transform = 'translateX(120%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 400);
        }, 4000);
    }
    
    static showLoading(message = 'Loading...') {
        // Remove existing loading if any
        this.hideLoading();
        
        const loading = document.createElement('div');
        loading.id = 'global-loading';
        loading.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            ">
                <div style="
                    background: white;
                    padding: 20px 30px;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                ">
                    <div style="
                        width: 20px;
                        height: 20px;
                        border: 2px solid #e5e7eb;
                        border-top: 2px solid #3b82f6;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 10px;
                    "></div>
                    <div>${message}</div>
                </div>
            </div>
        `;
        
        // Add CSS animation if not exists
        if (!document.querySelector('#loading-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-styles';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(loading);
    }
    
    static hideLoading() {
        const loading = document.getElementById('global-loading');
        if (loading) {
            loading.remove();
        }
    }
    
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

// Form handling functionality
class FormHandler {
    constructor() {
        this.forms = document.querySelectorAll('form');
        this.init();
    }

    init() {
        this.setupFormValidation();
        this.setupFileUploads();
        this.setupCharacterCounters();
        this.setupFormSubmissions();
    }

    setupFormValidation() {
        this.forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea');
            
            inputs.forEach(input => {
                // Real-time validation
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });

                input.addEventListener('input', () => {
                    this.clearFieldError(input);
                });
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
                errorMessage = 'This field is required';
        }

        if (fieldName === 'fileUpload' && value) {
            const isRelativePath = /^\/?uploads\//i.test(value);
            const isValidUrl = this.isValidUrl(value);
            if (!isRelativePath && !isValidUrl) {
                isValid = false;
                errorMessage = 'Enter a valid URL or leave empty to use uploaded file';
            }
        }

        // Username validation
        if (fieldName === 'username' && value) {
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'Username must be at least 3 characters';
            } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Username can only contain letters, numbers and underscores';
            }
        }

        // Password validation
        if (fieldName === 'password' && value) {
            if (value.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters';
            }
        }

        // URL validation
        if (fieldName !== 'fileUpload' && (
            fieldName.includes('url') || fieldName.includes('instagram') || fieldName.includes('discord') || 
            fieldName.includes('facebook') || fieldName.includes('steam') || fieldName.includes('twitch') || 
            fieldName.includes('tiktok') || fieldName.includes('telegram') || fieldName.includes('youtube') ||
            fieldName.includes('youtubeMusic') || fieldName.includes('viber') || fieldName.includes('googleDocs') ||
            fieldName.includes('googleSheets'))) {
            if (value && !this.isValidUrl(value)) {
                isValid = false;
                errorMessage = 'Enter a valid URL';
            }
        }

        // Email validation
        if (fieldName === 'email' && value) {
            if (!this.isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Enter a valid email address';
            }
        }

        this.showFieldError(field, isValid, errorMessage);
        return isValid;
    }

    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showFieldError(field, isValid, message) {
        const errorElement = document.getElementById(field.name + 'Error') || document.getElementById(field.id + 'Error');
        
        if (errorElement) {
            if (isValid) {
                field.classList.remove('error');
                field.classList.add('success');
                errorElement.classList.remove('show');
                errorElement.textContent = '';
            } else {
                // Show error to user
                console.log('Validation error for field:', field.name || field.id, message);
                field.classList.remove('success');
                field.classList.add('error');
                errorElement.classList.add('show');
                errorElement.textContent = message || 'Помилка валідації';
            }
        }
    }

    clearFieldError(field) {
        const errorElement = document.getElementById(field.name + 'Error');
        if (errorElement) {
            errorElement.classList.remove('show');
            field.classList.remove('error');
        }
    }

    setupFileUploads() {
        const fileInputs = document.querySelectorAll('.file-input');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                const label = input.nextElementSibling;
                const textSpan = label.querySelector('.file-text');
                if (textSpan && !textSpan.dataset.defaultText) {
                    textSpan.dataset.defaultText = textSpan.textContent;
                }
                const defaultText = textSpan ? textSpan.dataset.defaultText || 'Select File' : 'Select File';
                
                if (file) {
                    // Validate file type
                    if (input.accept && !this.isValidFileType(file, input.accept)) {
                        // Don't show error to user
                        console.log('Invalid file type');
                        input.value = '';
                        if (textSpan) {
                            textSpan.textContent = defaultText;
                        }
                        return;
                    }

                    // Validate file size (max 50MB for GIF and images)
                    if (file.size > 50 * 1024 * 1024) {
                        // Don't show error to user
                        console.log('File size exceeds 50MB');
                        input.value = '';
                        if (textSpan) {
                            textSpan.textContent = defaultText;
                        }
                        return;
                    }

                    if (textSpan) {
                        textSpan.textContent = file.name;
                    }
                    if (label) {
                        label.classList.add('has-file');
                    }
                } else {
                    if (textSpan) {
                        textSpan.textContent = defaultText;
                    }
                    if (label) {
                        label.classList.remove('has-file');
                    }
                }
            });
        });
    }

    isValidFileType(file, acceptTypes) {
        const types = acceptTypes.split(',').map(type => type.trim());
        return types.some(type => {
            if (type.startsWith('.')) {
                return file.name.toLowerCase().endsWith(type.toLowerCase());
            }
            return file.type.match(type.replace('*', '.*'));
        });
    }

    setupCharacterCounters() {
        const textareas = document.querySelectorAll('textarea[maxlength]');
        
        textareas.forEach(textarea => {
            const maxLength = parseInt(textarea.getAttribute('maxlength'));
            const counterId = textarea.name + 'Counter';
            const counter = document.getElementById(counterId);
            
            if (counter) {
                const updateCounter = () => {
                    const currentLength = textarea.value.length;
                    counter.textContent = currentLength;
                    
                    if (currentLength > maxLength * 0.9) {
                        counter.style.color = 'var(--warning-color)';
                    } else if (currentLength > maxLength * 0.8) {
                        counter.style.color = 'var(--accent-color)';
                    } else {
                        counter.style.color = 'var(--text-muted)';
                    }
                };

                textarea.addEventListener('input', updateCounter);
                updateCounter(); // Initial update
            }
        });
    }

    setupFormSubmissions() {
        this.forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmission(form);
            });
        });
    }

    async handleFormSubmission(form) {
        // Validate all fields
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        let isFormValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            // Don't show error to user, just log it
            console.log('Form validation failed');
            return;
        }

        // Show loading state
        form.classList.add('form-loading');
        const submitBtn = form.querySelector('button[type="submit"]') || document.querySelector('button[form="' + form.id + '"]');
        let originalText = '';
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>Processing...</span>';
        }
        
        // Show centered loading
        Utils.showLoading('Processing form...');

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Handle file uploads
            const files = {};
            const fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                if (input.files[0]) {
                    files[input.name] = input.files[0];
                }
            });

            // Determine form type and handle accordingly
            if (form.id === 'registrationForm') {
                await this.handleRegistration(data, files);
            } else if (form.id === 'loginForm') {
                await this.handleLogin(data);
            } else if (form.id === 'profileForm') {
                await this.handleProfileUpdate(data, files);
            }

        } catch (error) {
            console.error('Form submission error:', error);
            // Don't show error to user
            console.log('Form processing failed');
        } finally {
            // Reset form state
            form.classList.remove('form-loading');
            if (submitBtn && originalText) {
                submitBtn.innerHTML = originalText;
            }
            Utils.hideLoading();
        }
    }

    async handleRegistration(data, files) {
        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                body: this.createFormData(data, files)
            });

            // Try to parse JSON response regardless of HTTP status
            let result = null;
            try {
                const text = await response.text();
                result = text ? JSON.parse(text) : null;
            } catch (error) {
                console.error('Invalid JSON response:', error);
                Utils.showNotification('Помилка: сервер повернув некоректну відповідь', 'error');
                return;
            }

            if (result && result.success) {
                // Show success notification
                if (result.company && result.company.company_key) {
                    Utils.showNotification('Профіль компанії успішно створено!', 'success');
                } else {
                    Utils.showNotification('Профіль успішно створено!', 'success');
                }
                
                // Store user data
                localStorage.setItem('userID', data.username);
                localStorage.setItem('username', data.username);
                localStorage.setItem('password', data.password);
                if (result.user && typeof result.user.id !== 'undefined') {
                    localStorage.setItem('userNumericId', String(result.user.id));
                }
                
                // Store company info if exists
                if (result.company) {
                    localStorage.setItem('companyKey', result.company.company_key);
                    localStorage.setItem('companyId', result.company.company_id);
                }
                
                // Store full profile data for editor
                const profileData = {
                    username: data.username,
                    password: data.password,
                    description: data.description || '',
                    instagram: data.instagram || '',
                    discord: data.discord || '',
                    facebook: data.facebook || '',
                    steam: data.steam || '',
                    twitch: data.twitch || '',
                    tiktok: data.tiktok || '',
                    telegram: data.telegram || '',
                    youtube: data.youtube || '',
                    created_at: new Date().toISOString()
                };
                
                // Save profile data to localStorage and cookies
                localStorage.setItem('userProfile', JSON.stringify(profileData));
                this.setCookie('userProfile', JSON.stringify(profileData), 365);
                
                // Redirect to profile page
                setTimeout(() => {
                    window.location.href = 'my-profile.html';
                }, 2000);
            } else {
                const message = result && result.message ? result.message : 'Помилка реєстрації';
                console.error('Registration error:', message);
                // Show error to user
                Utils.showNotification(message, 'error');
                
                // Show specific field errors if available
                if (message.toLowerCase().includes('username')) {
                    this.showFieldError(document.getElementById('username'), false, message);
                }
                if (message.toLowerCase().includes('password')) {
                    this.showFieldError(document.getElementById('password'), false, message);
                }
                if (message.toLowerCase().includes('company')) {
                    const companyNameField = document.getElementById('companyName');
                    if (companyNameField) {
                        this.showFieldError(companyNameField, false, message);
                    }
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            Utils.showNotification('Помилка з\'єднання. Спробуйте ще раз.', 'error');
        }
    }

    async handleLogin(data) {
        try {
            const response = await fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                Utils.showNotification('Login successful!', 'success');
                
                // Store user data
                console.log('Storing user data:', {
                    userID: data.username,
                    username: data.username,
                    password: data.password
                });
                
                localStorage.setItem('userID', data.username);
                localStorage.setItem('username', data.username);
                localStorage.setItem('password', data.password);
                if (result.user && typeof result.user.id !== 'undefined') {
                    localStorage.setItem('userNumericId', String(result.user.id));
                }
                
                console.log('Stored in localStorage:', {
                    userID: localStorage.getItem('userID'),
                    username: localStorage.getItem('username'),
                    password: localStorage.getItem('password') ? 'Present' : 'Not present'
                });
                
                // Update auth buttons immediately
                if (window.BionrggApp && window.BionrggApp.updateAuthButtons) {
                    window.BionrggApp.updateAuthButtons();
                }
                
                // Redirect to profile page
                setTimeout(() => {
                    window.location.href = 'my-profile.html';
                }, 1500);
            } else {
                console.error('Login error:', result.message);
                // Show error to user
                Utils.showNotification(result.message || 'Невірний логін або пароль', 'error');
                
                // Show specific field errors
                const usernameField = document.getElementById('username');
                const passwordField = document.getElementById('password');
                
                if (result.message && (result.message.toLowerCase().includes('username') || result.message.toLowerCase().includes('user'))) {
                    if (usernameField) {
                        this.showFieldError(usernameField, false, result.message);
                    }
                }
                if (result.message && result.message.toLowerCase().includes('password')) {
                    if (passwordField) {
                        this.showFieldError(passwordField, false, result.message);
                    }
                } else if (result.message && result.message.toLowerCase().includes('invalid')) {
                    // Generic invalid credentials - show on both fields
                    if (usernameField) {
                        this.showFieldError(usernameField, false, 'Невірний логін або пароль');
                    }
                    if (passwordField) {
                        this.showFieldError(passwordField, false, 'Невірний логін або пароль');
                    }
                }
            }
        } catch (error) {
            console.error('Login error:', error);
                // Don't show error to user
                console.log('Connection error');
        }
    }

    async handleProfileUpdate(data, files) {
        try {
            const response = await fetch('api/update-profile.php', {
                method: 'POST',
                body: this.createFormData(data, files)
            });

            const result = await response.json();

            if (result.success) {
                Utils.showNotification('Profile updated successfully!', 'success');
            } else {
                console.error('Profile update error:', result.message);
                // Don't show error to user
            console.log('Form processing failed');
            }
        } catch (error) {
            console.error('Profile update error:', error);
                // Don't show error to user
                console.log('Connection error');
        }
    }

    createFormData(data, files) {
        const formData = new FormData();
        
        // Add regular data
        Object.entries(data).forEach(([key, value]) => {
            if (value !== '') {
                formData.append(key, value);
            }
        });

        // Add files
        Object.entries(files).forEach(([key, file]) => {
            formData.append(key, file);
        });

        return formData;
    }

    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }
}

// Initialize form handler when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FormHandler();
});

// Export for use in other scripts
window.FormHandler = FormHandler;
window.Utils = Utils;