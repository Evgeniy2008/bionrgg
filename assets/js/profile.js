// Profile viewing functionality
class ProfileViewer {
    constructor() {
        this.profileData = null;
        this.companyInfo = null;
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
        this.init();
    }

    init() {
        // Redirect old format to new format
        this.redirectOldUrl();
        this.loadProfile();
    }

    redirectOldUrl() {
        // If URL contains profile.html?username=, redirect to clean format
        const urlParams = new URLSearchParams(window.location.search);
        const usernameParam = urlParams.get('username');
        
        if (usernameParam && window.location.pathname.includes('profile.html')) {
            // Redirect to clean format: /username
            const newUrl = `/${usernameParam}`;
            if (window.location.pathname !== newUrl) {
                window.history.replaceState({}, '', newUrl);
            }
        }
    }

    async loadProfile() {
        // Get username from URL
        const username = this.getUsernameFromUrl();
        
        if (!username) {
            console.log('No username provided');
            this.showError('Профіль не знайдено');
            return;
        }

        console.log('Loading profile for:', username);

        try {
            const response = await fetch(`api/get-profile.php?username=${encodeURIComponent(username)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            console.log('Server response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Server response data:', data);
            
            if (data.success && data.profile) {
                console.log('Profile loaded successfully:', data.profile);
                console.log('Text background color in data:', data.profile.textBgColor);
                console.log('Text background opacity in data:', data.profile.textBgOpacity);
                this.profileData = data.profile;
                this.companyInfo = data.company || null;
                this.displayProfile();
                this.trackView();
            } else {
                console.error('Profile not found:', data.message);
                throw new Error('Profile not found');
            }

        } catch (error) {
            console.error('Error loading profile:', error);
            this.showError('Профіль не знайдено');
        }
    }

    getUsernameFromUrl() {
        // Priority 1: Try to get username from pathname (for URLs like /username)
        const pathname = window.location.pathname;
        const pathParts = pathname.split('/').filter(part => part);
        
        // Exclude known pages and directories
        const knownPages = ['index.html', 'create.html', 'profiles.html', 'my-profile.html', 'login.html', 'register.html', 'profile.html'];
        const knownDirs = ['api', 'assets', 'css', 'js', 'img'];
        
        if (pathParts.length > 0) {
            const lastPart = pathParts[pathParts.length - 1];
            // Check if it's not a known page, not a known directory, and not a file with extension
            if (!lastPart.includes('.') && 
                !knownPages.includes(lastPart.toLowerCase()) && 
                !knownDirs.includes(lastPart.toLowerCase())) {
                // Validate username format (alphanumeric, underscore, hyphen)
                if (/^[a-zA-Z0-9_-]+$/.test(lastPart)) {
                    return lastPart;
                }
            }
        }

        // Priority 2: Try to get username from URL parameters (legacy support)
        const urlParams = new URLSearchParams(window.location.search);
        const usernameParam = urlParams.get('username');
        if (usernameParam) {
            return usernameParam;
        }
        
        const userParam = urlParams.get('user');
        if (userParam) {
            return userParam;
        }

        return null;
    }

    displayProfile() {
        console.log('Displaying profile:', this.profileData);
        
        if (!this.profileData) {
            console.log('No profile data to display');
            return;
        }

        // Hide loading
        document.getElementById('profileLoading').style.display = 'none';
        
        // Show profile content
        const profileContent = document.getElementById('profileContent');
        profileContent.style.display = 'block';

        // Update profile info
        document.getElementById('profileName').textContent = this.profileData.username;
        document.getElementById('profileDescription').textContent = this.profileData.descr || this.profileData.description || 'No description';
        
        console.log('Profile info updated:', {
            username: this.profileData.username,
            description: this.profileData.descr || this.profileData.description
        });

        // Update avatar
        const avatarImg = document.getElementById('avatarImage');
        if (this.profileData.avatar) {
            avatarImg.src = `data:image/jpeg;base64,${this.profileData.avatar}`;
        } else {
            avatarImg.src = 'assets/img/profile.png';
        }

        // Update profile background
        if (this.profileData.bg) {
            const container = document.getElementById('profileContainer');
            container.style.backgroundImage = `url(data:image/jpeg;base64,${this.profileData.bg})`;
            container.style.backgroundSize = 'cover';
            container.style.backgroundPosition = 'center';
            container.style.backgroundRepeat = 'no-repeat';
            console.log('Background image set');
        }
        
        // Update block image
        if (this.profileData.blockImage || this.profileData.block_image) {
            const card = document.getElementById('profileCard');
            if (card) {
                const blockImage = this.profileData.blockImage || this.profileData.block_image;
                card.style.backgroundImage = `url(data:image/jpeg;base64,${blockImage})`;
                card.style.backgroundSize = 'cover';
                card.style.backgroundPosition = 'center';
                card.style.backgroundRepeat = 'no-repeat';
                console.log('Block image set');
            }
        }

        // Update profile colors with opacity
        if (this.profileData.color) {
            const profileColor = this.profileData.color;
            const profileOpacity = this.profileData.profileOpacity || 100;
            const profileColorRgba = this.hexToRgba(profileColor, profileOpacity / 100);
            document.getElementById('profileCard').style.backgroundColor = profileColorRgba;
            console.log('Profile color set:', profileColorRgba);
        }
        
        // Apply text colors and background
        const profileName = document.getElementById('profileName');
        const profileDescription = document.getElementById('profileDescription');
        
        // Get text background values
        const textBgColor = this.profileData.textBgColor || '';
        const textBgOpacity = this.profileData.textBgOpacity !== undefined ? this.profileData.textBgOpacity : 100;
        
        // Debug logging - detailed check
        console.log('=== TEXT BACKGROUND DEBUG ===');
        console.log('Full profileData:', this.profileData);
        console.log('textBgColor from data:', this.profileData.textBgColor);
        console.log('textBgOpacity from data:', this.profileData.textBgOpacity);
        console.log('textBgColor processed:', textBgColor);
        console.log('textBgOpacity processed:', textBgOpacity);
        console.log('Has textBgColor:', !!textBgColor && textBgColor.trim() !== '');
        console.log('profileName element:', profileName);
        console.log('profileDescription element:', profileDescription);
        console.log('=== END TEXT BACKGROUND DEBUG ===');
        
        // Apply text color first
        if (this.profileData.colorText) {
            const textColor = this.profileData.colorText;
            const textOpacity = this.profileData.textOpacity || 100;
            const textColorRgba = this.hexToRgba(textColor, textOpacity / 100);
            
            if (profileName) {
                profileName.style.color = textColorRgba;
            }
            
            if (profileDescription) {
                profileDescription.style.color = textColorRgba;
            }
            
            console.log('Text color set:', textColorRgba);
        }

        const companySection = document.getElementById('profileCompany');
        const companyNameEl = document.getElementById('profileCompanyName');
        const companyTaglineEl = document.getElementById('profileCompanyTagline');
        const companyLogoWrapper = document.getElementById('profileCompanyLogoWrapper');
        const companyLogoImg = document.getElementById('profileCompanyLogo');

        if (companySection && companyNameEl && companyTaglineEl && companyLogoWrapper && companyLogoImg) {
            const branding = this.resolveCompanyBranding();
            const hasName = branding.name && branding.name.trim() !== '';
            const hasTagline = branding.tagline && branding.tagline.trim() !== '';
            const showName = branding.showName && hasName;
            const showLogo = branding.showLogo && !!branding.logo;

            const textColorHex = this.profileData.colorText || this.profileData.textColor || '#ffffff';
            const textOpacity = this.profileData.textOpacity !== undefined ? this.profileData.textOpacity : 100;
            const textColorRgba = this.hexToRgba(textColorHex, textOpacity / 100);

            if (showName) {
                companyNameEl.textContent = branding.name;
                companyNameEl.style.display = 'block';
                companyNameEl.style.setProperty('color', textColorRgba, 'important');
            } else {
                companyNameEl.textContent = '';
                companyNameEl.style.display = 'none';
                companyNameEl.style.removeProperty('color');
            }

            if (hasTagline) {
                companyTaglineEl.textContent = branding.tagline;
                companyTaglineEl.style.display = 'block';
                companyTaglineEl.style.setProperty('color', textColorRgba, 'important');
            } else {
                companyTaglineEl.textContent = '';
                companyTaglineEl.style.display = 'none';
                companyTaglineEl.style.removeProperty('color');
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

            companySection.style.display = (showName || showLogo || hasTagline) ? 'flex' : 'none';
        }
        
        // Apply text background if set (works independently of text color)
        // Use setTimeout to ensure DOM is fully ready and styles can be applied
        setTimeout(() => {
            // Only apply if textBgColor is set and not empty
            if (textBgColor && textBgColor.trim() !== '' && textBgColor.trim() !== 'null' && textBgColor !== null) {
                const textBgColorRgba = this.hexToRgba(textBgColor, textBgOpacity / 100);
                console.log('Applying text background color:', textBgColorRgba);
                console.log('Color hex:', textBgColor);
                console.log('Opacity:', textBgOpacity);
                
                const nameEl = document.getElementById('profileName');
                const descEl = document.getElementById('profileDescription');
                const companyNameEl = document.getElementById('profileCompanyName');
                const companyTaglineEl = document.getElementById('profileCompanyTagline');
                
                if (nameEl) {
                    nameEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    nameEl.style.setProperty('padding', '4px 8px', 'important');
                    nameEl.style.setProperty('border-radius', '4px', 'important');
                    nameEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    console.log('✅ Text background applied to name');
                    console.log('Name computed style after:', window.getComputedStyle(nameEl).backgroundColor);
                } else {
                    console.error('❌ profileName element not found!');
                }
                
                if (descEl) {
                    descEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    descEl.style.setProperty('padding', '4px 8px', 'important');
                    descEl.style.setProperty('border-radius', '4px', 'important');
                    descEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    console.log('✅ Text background applied to description');
                    console.log('Description computed style after:', window.getComputedStyle(descEl).backgroundColor);
                } else {
                    console.error('❌ profileDescription element not found!');
                }
                
                if (companyNameEl) {
                    companyNameEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    companyNameEl.style.setProperty('padding', '4px 8px', 'important');
                    companyNameEl.style.setProperty('border-radius', '4px', 'important');
                    companyNameEl.style.setProperty('box-decoration-break', 'clone', 'important');
                }
                
                if (companyTaglineEl) {
                    companyTaglineEl.style.setProperty('background-color', textBgColorRgba, 'important');
                    companyTaglineEl.style.setProperty('padding', '4px 8px', 'important');
                    companyTaglineEl.style.setProperty('border-radius', '4px', 'important');
                    companyTaglineEl.style.setProperty('box-decoration-break', 'clone', 'important');
                }
            } else {
                console.log('⚠️ No text background color set, clearing styles');
                // Remove text background if not set
                const nameEl = document.getElementById('profileName');
                const descEl = document.getElementById('profileDescription');
                const companyNameEl = document.getElementById('profileCompanyName');
                const companyTaglineEl = document.getElementById('profileCompanyTagline');
                
                if (nameEl) {
                    nameEl.style.removeProperty('background-color');
                    nameEl.style.removeProperty('padding');
                    nameEl.style.removeProperty('border-radius');
                    nameEl.style.removeProperty('box-decoration-break');
                }
                if (descEl) {
                    descEl.style.removeProperty('background-color');
                    descEl.style.removeProperty('padding');
                    descEl.style.removeProperty('border-radius');
                    descEl.style.removeProperty('box-decoration-break');
                }
                if (companyNameEl) {
                    companyNameEl.style.removeProperty('background-color');
                    companyNameEl.style.removeProperty('padding');
                    companyNameEl.style.removeProperty('border-radius');
                    companyNameEl.style.removeProperty('box-decoration-break');
                }
                if (companyTaglineEl) {
                    companyTaglineEl.style.removeProperty('background-color');
                    companyTaglineEl.style.removeProperty('padding');
                    companyTaglineEl.style.removeProperty('border-radius');
                    companyTaglineEl.style.removeProperty('box-decoration-break');
                }
            }
        }, 100);

        this.updateCustomLogoDisplay();

        // Update social links
        this.updateSocialLinks();

        // Update page title
        document.title = `${this.profileData.username} | Bionrgg`;
        
        // Double-check text background after all updates (in case styles were overwritten)
        setTimeout(() => {
            const textBgColor = this.profileData.textBgColor || '';
            if (textBgColor && textBgColor.trim() !== '') {
                const textBgOpacity = this.profileData.textBgOpacity !== undefined ? this.profileData.textBgOpacity : 100;
                const textBgColorRgba = this.hexToRgba(textBgColor, textBgOpacity / 100);
                
                const nameEl = document.getElementById('profileName');
                const descEl = document.getElementById('profileDescription');
                const companyNameEl = document.getElementById('profileCompanyName');
                const companyTaglineEl = document.getElementById('profileCompanyTagline');
                
                if (nameEl) {
                    const currentBg = window.getComputedStyle(nameEl).backgroundColor;
                    if (currentBg === 'rgba(0, 0, 0, 0)' || currentBg === 'transparent') {
                        console.log('🔄 Re-applying text background to name (was cleared)');
                        nameEl.style.setProperty('background-color', textBgColorRgba, 'important');
                        nameEl.style.setProperty('padding', '4px 8px', 'important');
                        nameEl.style.setProperty('border-radius', '4px', 'important');
                        nameEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    }
                }
                
                if (descEl) {
                    const currentBg = window.getComputedStyle(descEl).backgroundColor;
                    if (currentBg === 'rgba(0, 0, 0, 0)' || currentBg === 'transparent') {
                        console.log('🔄 Re-applying text background to description (was cleared)');
                        descEl.style.setProperty('background-color', textBgColorRgba, 'important');
                        descEl.style.setProperty('padding', '4px 8px', 'important');
                        descEl.style.setProperty('border-radius', '4px', 'important');
                        descEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    }
                }
                
                if (companyNameEl) {
                    const currentBg = window.getComputedStyle(companyNameEl).backgroundColor;
                    if (currentBg === 'rgba(0, 0, 0, 0)' || currentBg === 'transparent') {
                        companyNameEl.style.setProperty('background-color', textBgColorRgba, 'important');
                        companyNameEl.style.setProperty('padding', '4px 8px', 'important');
                        companyNameEl.style.setProperty('border-radius', '4px', 'important');
                        companyNameEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    }
                }
                
                if (companyTaglineEl) {
                    const currentBg = window.getComputedStyle(companyTaglineEl).backgroundColor;
                    if (currentBg === 'rgba(0, 0, 0, 0)' || currentBg === 'transparent') {
                        companyTaglineEl.style.setProperty('background-color', textBgColorRgba, 'important');
                        companyTaglineEl.style.setProperty('padding', '4px 8px', 'important');
                        companyTaglineEl.style.setProperty('border-radius', '4px', 'important');
                        companyTaglineEl.style.setProperty('box-decoration-break', 'clone', 'important');
                    }
                }
            }
        }, 500);
    }

    updateSocialLinks() {
        console.log('Updating social links...');
        
        const linksContainer = document.getElementById('profileLinks');
        if (!linksContainer) {
            console.log('Links container not found');
            return;
        }
        
        const socialLinks = this.getSocialLinks();
        console.log('Social links found:', socialLinks);
        
        if (socialLinks.length === 0) {
            linksContainer.innerHTML = '<p style="color: var(--text-muted); font-size: var(--font-size-sm);">No links added</p>';
            console.log('No social links found');
            return;
        }

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
                    <a href="${link.href}" class="profile-link" ${attributes.join(' ')}>
                        <img src="${link.icon}" alt="${link.name}">
                        <span>${label}</span>
                    </a>
                `;
            }

            const valueText = link.displayValue || link.url || '';
            const textLabel = valueText ? `${link.name}: ${valueText}` : label;
            return `
                <div class="profile-link" style="cursor: default;">
                    <img src="${link.icon}" alt="${link.name}">
                    <span>${textLabel}</span>
                </div>
            `;
        }).join('');

        linksContainer.innerHTML = linksHTML;
        console.log('Social links updated successfully');
        
        // Apply social styling after links are created
        this.applySocialStyling();
    }
    
    applySocialStyling() {
        console.log('Applying social styling...');
        console.log('Full profileData:', this.profileData);
        console.log('Profile data for styling:', {
            socialBgColor: this.profileData.socialBgColor,
            socialTextColor: this.profileData.socialTextColor,
            socialOpacity: this.profileData.socialOpacity,
            socialBgImage: this.profileData.socialBgImage
        });
        
        console.log('Raw color values:', {
            socialBgColor: `"${this.profileData.socialBgColor}" (type: ${typeof this.profileData.socialBgColor})`,
            socialTextColor: `"${this.profileData.socialTextColor}" (type: ${typeof this.profileData.socialTextColor})`
        });
        
        const linksContainer = document.getElementById('profileLinks');
        if (!linksContainer) {
            console.log('Links container not found for styling');
            return;
        }
        
        // Apply styles to individual links only (not to container)
        const profileLinks = linksContainer.querySelectorAll('.profile-link');
        console.log('Found profile links:', profileLinks.length);
        
        if (profileLinks.length === 0) {
            console.log('No links found to apply styles to');
            return;
        }
        
        // Get social styling values - check if they exist first
        const socialBgColor = (this.profileData.socialBgColor && this.profileData.socialBgColor !== '') ? this.profileData.socialBgColor : '#000000';
        const socialTextColor = (this.profileData.socialTextColor && this.profileData.socialTextColor !== '') ? this.profileData.socialTextColor : '#ffffff';
        const socialOpacity = this.profileData.socialOpacity || 90;
        
        console.log('Applying social styles:', {
            socialBgColor,
            socialTextColor,
            socialOpacity
        });
        
        // Convert background color to rgba
        const socialBgColorRgba = this.hexToRgba(socialBgColor, socialOpacity / 100);
        
        profileLinks.forEach((link, index) => {
            console.log(`Applying styles to link ${index + 1}`);
            console.log(`Link element:`, link);
            console.log(`Link computed styles before:`, window.getComputedStyle(link));
            
            // Apply background image if exists
            if (this.profileData.socialBgImage) {
                link.style.setProperty('background-image', `url(data:image/jpeg;base64,${this.profileData.socialBgImage})`, 'important');
                link.style.setProperty('background-size', 'cover', 'important');
                link.style.setProperty('background-position', 'center', 'important');
                link.style.setProperty('background-repeat', 'no-repeat', 'important');
                console.log(`Link ${index + 1} background image set`);
            }
            
            // Apply background color with opacity (only if no background image)
            if (!this.profileData.socialBgImage) {
                link.style.setProperty('background-color', socialBgColorRgba, 'important');
                console.log(`Link ${index + 1} background set to:`, {
                    original: socialBgColor,
                    opacity: socialOpacity,
                    rgba: socialBgColorRgba
                });
            }
            
            // Apply text color
            link.style.setProperty('color', socialTextColor, 'important');
            console.log(`Link ${index + 1} text color set to:`, socialTextColor);
            
            console.log(`Link computed styles after:`, window.getComputedStyle(link));
            console.log(`Link inline styles:`, link.style.cssText);
        });
        
        console.log('Social styling applied successfully');
    }

    getSocialPlatformDefinitions() {
        return {
            // Popular Platforms
            instagram: { name: 'Instagram', icon: 'assets/img/insta.png' },
            youtube: { name: 'YouTube', icon: 'assets/img/youtube.png' },
            tiktok: { name: 'TikTok', icon: 'assets/img/tiktok.png' },
            facebook: { name: 'Facebook', icon: 'assets/img/facebook.png' },
            x: { name: 'X (Twitter)', icon: 'assets/img/x.png' },
            linkedin: { name: 'LinkedIn', icon: 'assets/img/linkedin.png' },
            
            // Messaging & Chats
            telegram: { name: 'Telegram', icon: 'assets/img/tg.png' },
            whatsapp: { name: 'WhatsApp', icon: 'assets/img/whatsapp.png' },
            viber: { name: 'Viber', icon: 'assets/img/viber.png' },
            discord: { name: 'Discord', icon: 'assets/img/discord.png' },
            
            // Gaming & Streaming
            twitch: { name: 'Twitch', icon: 'assets/img/twitch.png' },
            steam: { name: 'Steam', icon: 'assets/img/steam.png' },
            
            // Music & Audio
            spotify: { name: 'Spotify', icon: 'assets/img/spotify.png' },
            soundcloud: { name: 'SoundCloud', icon: 'assets/img/soundcloud.png' },
            youtubeMusic: { name: 'YouTube Music', icon: 'assets/img/youtubeMusic.png' },
            
            // Development & Tech
            github: { name: 'GitHub', icon: 'assets/img/github.png' },
            site: { name: 'Website', icon: 'assets/img/site.png' },
            
            // Documents & Files
            googleDocs: { name: 'Google Docs', icon: 'assets/img/google docs.png' },
            googleSheets: { name: 'Google Sheets', icon: 'assets/img/googlesheets.png' },
            fileUpload: { name: 'Файл', icon: 'assets/img/file.png' },
            
            // Freelance & Work
            upwork: { name: 'Upwork', icon: 'assets/img/upwork.png' },
            fiverr: { name: 'Fiverr', icon: 'assets/img/fiverr.png' },
            djinni: { name: 'Djinni', icon: 'assets/img/djinni.png' },
            
            // Other Platforms
            reddit: { name: 'Reddit', icon: 'assets/img/reddit.png' },
            dou: { name: 'DOU', icon: 'assets/img/dou.png' },
            olx: { name: 'OLX', icon: 'assets/img/olx.png' },
            amazon: { name: 'Amazon', icon: 'assets/img/amazon.png' },
            prom: { name: 'Prom.ua', icon: 'assets/img/prom.png' },
            fhunt: { name: 'FHunt', icon: 'assets/img/fhunt.png' },
            dj: { name: 'DJ', icon: 'assets/img/dj.png' },
            
            // Banks
            privatBank: { name: 'ПриватБанк', icon: 'assets/img/privatBank.png' },
            monoBank: { name: 'Монобанк', icon: 'assets/img/monoBank.png' },
            alfaBank: { name: 'Альфа-Банк', icon: 'assets/img/alfaBank.png' },
            abank: { name: 'А-Банк', icon: 'assets/img/abank.png' },
            pumbBank: { name: 'ПУМБ', icon: 'assets/img/pumbBank.png' },
            raiffeisenBank: { name: 'Райффайзен Банк', icon: 'assets/img/raiffeisenBank.png' },
            senseBank: { name: 'Sense Bank', icon: 'assets/img/senseBank.png' },
            
            // Cryptocurrency Exchanges
            binance: { name: 'Binance', icon: 'assets/img/binance.png' },
            trustWallet: { name: 'Trust Wallet', icon: 'assets/img/trustWallet.png' }
        };
    }

    getSocialLinks() {
        console.log('Getting social links for profile:', this.profileData);
        
        const links = [];
        const socialPlatforms = this.getSocialPlatformDefinitions();

        Object.entries(socialPlatforms).forEach(([key, platform]) => {
            let value = this.profileData ? this.profileData[key] : '';

            if (!value && this.profileData) {
                if (key === 'instagram' && this.profileData.inst) value = this.profileData.inst;
                if (key === 'facebook' && this.profileData.fb) value = this.profileData.fb;
                if (key === 'telegram' && this.profileData.tg) value = this.profileData.tg;
                if (key === 'x' && this.profileData.twitter) value = this.profileData.twitter;
            }

            if (value === undefined || value === null) {
                return;
            }

            value = String(value).trim();
            if (value === '') {
                return;
            }

            if (key === 'fileUpload') {
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
                return;
            }

            const normalizedUrl = this.normalizeUrl(value, key);
            const isUrl = normalizedUrl.startsWith('http://') || normalizedUrl.startsWith('https://');
            links.push({
                ...platform,
                url: normalizedUrl,
                href: isUrl ? normalizedUrl : '',
                displayName: platform.name,
                displayValue: normalizedUrl
            });
        });

        const extraLinks = this.getExtraLinksFromProfile();
        if (extraLinks.length > 0) {
            extraLinks.forEach((entry) => {
                if (!entry || typeof entry !== 'object') {
                    return;
                }

                const platformKey = entry.platform && socialPlatforms[entry.platform] ? entry.platform : null;
                const basePlatform = platformKey
                    ? socialPlatforms[platformKey]
                    : {
                        name: entry.label && entry.label.trim() ? entry.label.trim() : 'Посилання',
                        icon: (socialPlatforms.site && socialPlatforms.site.icon) || 'assets/img/site.png'
                    };

                const labelText = entry.label && entry.label.trim() ? entry.label.trim() : basePlatform.name;
                const type = entry.type === 'file' ? 'file' : 'link';

                if (type === 'file') {
                    const storedPath = entry.storedFilePath ? String(entry.storedFilePath).trim() : '';
                    if (!storedPath) {
                        return;
                    }
                    const href = this.formatFileUrl(storedPath);
                    const fileName = entry.originalName && entry.originalName.trim()
                        ? entry.originalName.trim()
                        : this.getFileDisplayName(storedPath);
                    const displayName = labelText ? `${labelText}: ${fileName}` : fileName;

                    links.push({
                        ...basePlatform,
                        url: storedPath,
                        href,
                        isFile: true,
                        displayName,
                        displayValue: fileName,
                        downloadName: fileName
                    });
                    return;
                }

                const url = entry.url ? String(entry.url).trim() : '';
                if (!url) {
                    return;
                }

                const normalizedUrl = this.normalizeUrl(url, entry.platform || '');
                const isUrl = normalizedUrl.startsWith('http://') || normalizedUrl.startsWith('https://');

                links.push({
                    ...basePlatform,
                    url: normalizedUrl,
                    href: isUrl ? normalizedUrl : '',
                    displayName: labelText,
                    displayValue: normalizedUrl
                });
            });
        }

        return links;
    }

    updateCustomLogoDisplay() {
        const logoWrapper = document.getElementById('profileCustomLogo');
        const logoImg = document.getElementById('profileCustomLogoImg');

        if (!logoWrapper || !logoImg) {
            return;
        }

        const position = this.profileData && this.profileData.customLogoPosition
            ? this.profileData.customLogoPosition
            : 'none';

        let size = this.profileData && this.profileData.customLogoSize
            ? Number(this.profileData.customLogoSize)
            : 90;
        if (Number.isNaN(size) || size <= 0) {
            size = 90;
        }
        size = Math.round(size);

        let source = null;
        if (this.profileData && this.profileData.customLogo) {
            source = this.formatImageSource(this.profileData.customLogo);
        }

        logoWrapper.classList.remove('logo-visible', ...this.logoPositionClasses);
        logoWrapper.style.display = 'none';
        logoWrapper.style.width = '';
        logoImg.src = '';

        if (!source || position === 'none') {
            return;
        }

        let normalizedPosition = `logo-position-${position}`;
        if (!this.logoPositionClasses.includes(normalizedPosition)) {
            normalizedPosition = 'logo-position-middle-center';
        }

        logoWrapper.style.display = 'block';
        logoWrapper.style.width = `${size}px`;
        logoWrapper.classList.add('logo-visible', normalizedPosition);
        logoImg.src = source;
    }

    getExtraLinksFromProfile() {
        if (!this.profileData) {
            return [];
        }

        let extraLinks = this.profileData.extraLinks ?? [];
        if (!extraLinks) {
            return [];
        }

        if (typeof extraLinks === 'string') {
            try {
                extraLinks = JSON.parse(extraLinks);
            } catch (error) {
                console.warn('Failed to parse extraLinks JSON on profile view', error);
                extraLinks = [];
            }
        }

        if (!Array.isArray(extraLinks)) {
            return [];
        }

        return extraLinks;
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
            return `https://${url}`;
        }
        
        return url;
    }

    resolveCompanyBranding() {
        const branding = {
            name: '',
            tagline: '',
            logo: null,
            showLogo: false,
            showName: false
        };

        if (this.companyInfo) {
            branding.name = this.companyInfo.name || branding.name;
            if (this.companyInfo.design) {
                const design = this.companyInfo.design;
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

        if (!branding.name && this.companyInfo && this.companyInfo.name) {
            branding.name = this.companyInfo.name;
        }

        if (!branding.showName && branding.name) {
            branding.showName = true;
        }

        return branding;
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

        const normalized = value.trim().replace(/\\/g, '/');

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

    getFileDisplayName(value) {
        if (!value || typeof value !== 'string') {
            return '';
        }
        const normalized = value.replace(/\\/g, '/');
        const segments = normalized.split('/');
        return segments.pop() || normalized;
    }

    hexToRgba(hex, opacity) {
        // Validate input
        if (!hex || typeof hex !== 'string') {
            console.warn('Invalid hex color:', hex);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Remove # if present
        hex = hex.replace('#', '');
        
        // Ensure hex is 6 characters
        if (hex.length !== 6) {
            console.warn('Invalid hex length:', hex);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Validate hex characters
        if (!/^[0-9A-Fa-f]{6}$/.test(hex)) {
            console.warn('Invalid hex characters:', hex);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Convert hex to RGB
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        // Validate RGB values
        if (isNaN(r) || isNaN(g) || isNaN(b)) {
            console.warn('Invalid RGB values:', { r, g, b, hex });
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

    async trackView() {
        if (!this.profileData || !this.profileData.username) return;

        try {
            await fetch('api/track-view.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: this.profileData.username
                })
            });
        } catch (error) {
            console.error('Error tracking view:', error);
        }
    }

    showError(message) {
        // Hide loading
        const loadingEl = document.getElementById('profileLoading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        
        // Show error
        const errorDiv = document.getElementById('profileError');
        if (errorDiv) {
            errorDiv.style.display = 'block';
            
            // Update error message with friendly text
            const errorText = errorDiv.querySelector('p');
            if (errorText) {
                errorText.textContent = 'Профіль не знайдено. Користувача з таким ім\'ям не існує або профіль було видалено.';
            }
        }
    }
}

// Initialize profile viewer when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProfileViewer();
});

// Export for use in other scripts
window.ProfileViewer = ProfileViewer;