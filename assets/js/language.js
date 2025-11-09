// Global Language System
class LanguageManager {
    constructor() {
        this.currentLanguage = 'en';
        this.translations = {
            en: {
                // Navigation
                home: 'Home',
                profiles: 'Profiles',
                leaders: 'Leaders',
                login: 'Login',
                create: 'Create Profile',
                editor: 'Editor',
                
                // Hero
                hero_subtitle: '— create your stylish profile, collect all links and share it for free',
                hero_description: 'Combine all your social networks in one place. Perfect for bloggers, gamers and entrepreneurs!',
                
                // Common
                loading: 'Loading...',
                save: 'Save',
                cancel: 'Cancel',
                edit: 'Edit',
                delete: 'Delete',
                close: 'Close',
                back: 'Back',
                next: 'Next',
                previous: 'Previous',
                search: 'Search',
                submit: 'Submit',
                reset: 'Reset',
                preview: 'Preview',
                fullscreen: 'Fullscreen',
                
                // Profile
                profile: 'Profile',
                my_profile: 'My Profile',
                edit_profile: 'Edit Profile',
                profile_editor: 'Profile Editor',
                avatar: 'Avatar',
                name: 'Name',
                bio: 'Bio',
                social_links: 'Social Links',
                stats: 'Stats',
                achievements: 'Achievements',
                views: 'views',
                likes: 'likes',
                posts: 'posts',
                
                // Forms
                username: 'Username',
                password: 'Password',
                email: 'Email',
                description: 'Description',
                website: 'Website',
                phone: 'Phone',
                
                // Form titles
                create_profile_title: 'Create your digital business card in 1 minute',
                create_profile_subtitle: 'Join Bionrgg – combine all your social networks in one profile.',
                
                // Form fields
                basic_info: 'Basic Information',
                username_required: 'Username *',
                password_required: 'Password *',
                profile_description: 'Profile Description',
                tell_about_yourself: 'Tell about yourself...',
                social_networks: 'Social Networks',
                create_profile: 'Create Profile',
                
                // Login
                login_to_bionrgg: 'Login to Bionrgg',
                login_subtitle: 'Manage your digital business card and combine all social networks in one place.',
                
                // Profiles
                leaders_table: 'LEADERS TABLE | TOP 5',
                leaders_subtitle: 'Leaders table based on views',
                loading_profiles: 'Loading profiles...',
                loading_error: 'Loading Error',
                loading_error_text: 'Failed to load profiles. Please try again later.',
                try_again: 'Try Again',
                
                // My Profile
                edit_profile: 'Edit Profile',
                edit_profile_subtitle: 'Update information and customize your digital business card design',
                edit_design: 'Edit Design',
                
                // Additional texts
                no_account: 'No account?',
                how_to_create_profile: 'How to create your profile?',
                simple_process: 'Simple process of creating your digital business card',
                click_create_profile: 'Click "Create Profile"',
                go_to_registration: 'Go to the registration page and fill in the basic information',
                fill_all_fields: 'Fill in all fields',
                add_description_links: 'Add description, social media links and customize design',
                enjoy_using: 'Enjoy using :)',
                share_profile_views: 'Share your profile and collect views',
                
                // Design
                background: 'Background',
                theme: 'Theme',
                color: 'Color',
                gradient: 'Gradient',
                solid: 'Solid',
                image: 'Image',
                dark: 'Dark',
                light: 'Light',
                neon: 'Neon',
                
                // Messages
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Info',
                profile_saved: 'Profile saved successfully!',
                profile_updated: 'Profile updated successfully!',
                profile_deleted: 'Profile deleted successfully!',
                login_success: 'Login successful!',
                logout_success: 'Logout successful!',
                registration_success: 'Registration successful!',
                
                // Errors
                login_required: 'Login required',
                access_denied: 'Access denied',
                profile_not_found: 'Profile not found',
                user_not_found: 'User not found',
                invalid_credentials: 'Invalid credentials',
                username_taken: 'Username already taken',
                password_too_short: 'Password too short',
                email_invalid: 'Invalid email',
                required_field: 'This field is required',
                min_length: 'Minimum length is {min} characters',
                max_length: 'Maximum length is {max} characters',
                
                // Actions
                create_profile: 'Create Profile',
                edit_design: 'Edit Design',
                copy_link: 'Copy Link',
                share_profile: 'Share Profile',
                view_profile: 'View Profile',
                update_profile: 'Update Profile',
                delete_profile: 'Delete Profile',
                
                // Footer
                about: 'About',
                contact: 'Contact',
                privacy: 'Privacy',
                terms: 'Terms',
                support: 'Support',
                help: 'Help',
                faq: 'FAQ',
                documentation: 'Documentation',
                copyright: 'All rights reserved.'
            },
            ru: {
                // Navigation
                home: 'Главная',
                profiles: 'Профили',
                leaders: 'Лидеры',
                login: 'Войти',
                create: 'Создать профиль',
                editor: 'Редактор',
                
                // Hero
                hero_subtitle: '— создайте свой стильный профиль, соберите все ссылки и поделитесь им бесплатно',
                hero_description: 'Объедините все свои соцсети в одном месте. Идеально для блогеров, геймеров и предпринимателей!',
                
                // Common
                loading: 'Загрузка...',
                save: 'Сохранить',
                cancel: 'Отмена',
                edit: 'Редактировать',
                delete: 'Удалить',
                close: 'Закрыть',
                back: 'Назад',
                next: 'Далее',
                previous: 'Назад',
                search: 'Поиск',
                submit: 'Отправить',
                reset: 'Сбросить',
                preview: 'Предпросмотр',
                fullscreen: 'Полный экран',
                
                // Profile
                profile: 'Профиль',
                my_profile: 'Мой профиль',
                edit_profile: 'Редактировать профиль',
                profile_editor: 'Редактор профиля',
                avatar: 'Аватар',
                name: 'Имя',
                bio: 'О себе',
                social_links: 'Социальные ссылки',
                stats: 'Статистика',
                achievements: 'Достижения',
                views: 'просмотров',
                likes: 'лайков',
                posts: 'постов',
                
                // Forms
                username: 'Имя пользователя',
                password: 'Пароль',
                email: 'Email',
                description: 'Описание',
                website: 'Веб-сайт',
                phone: 'Телефон',
                
                // Form titles
                create_profile_title: 'Создайте свою цифровую визитку за 1 минуту',
                create_profile_subtitle: 'Присоединяйтесь к Bionrgg – объедините все свои соцсети в одном профиле.',
                
                // Form fields
                basic_info: 'Основная информация',
                username_required: 'Имя пользователя *',
                password_required: 'Пароль *',
                profile_description: 'Описание профиля',
                tell_about_yourself: 'Расскажите о себе...',
                social_networks: 'Социальные сети',
                create_profile: 'Создать профиль',
                
                // Login
                login_to_bionrgg: 'Войти в Bionrgg',
                login_subtitle: 'Управляйте своей цифровой визиткой и объедините все соцсети в одном месте.',
                
                // Profiles
                leaders_table: 'ТАБЛИЦА ЛИДЕРОВ | ТОП 5',
                leaders_subtitle: 'Таблица лидеров на основе просмотров',
                loading_profiles: 'Загрузка профилей...',
                loading_error: 'Ошибка загрузки',
                loading_error_text: 'Не удалось загрузить профили. Попробуйте позже.',
                try_again: 'Попробовать снова',
                
                // My Profile
                edit_profile: 'Редактировать профиль',
                edit_profile_subtitle: 'Обновите информацию и настройте дизайн вашей цифровой визитки',
                edit_design: 'Редактировать дизайн',
                
                // Additional texts
                no_account: 'Нет аккаунта?',
                how_to_create_profile: 'Как создать свой профиль?',
                simple_process: 'Простой процесс создания вашей цифровой визитки',
                click_create_profile: 'Нажмите «Создать профиль»',
                go_to_registration: 'Перейдите на страницу регистрации и заполните базовую информацию',
                fill_all_fields: 'Заполните все поля',
                add_description_links: 'Добавьте описание, ссылки на соцсети и настройте дизайн',
                enjoy_using: 'Пользуйтесь с удовольствием :)',
                share_profile_views: 'Поделитесь своим профилем и собирайте просмотры',
                
                // Design
                background: 'Фон',
                theme: 'Тема',
                color: 'Цвет',
                gradient: 'Градиент',
                solid: 'Сплошной',
                image: 'Изображение',
                dark: 'Темная',
                light: 'Светлая',
                neon: 'Неон',
                
                // Messages
                success: 'Успех',
                error: 'Ошибка',
                warning: 'Предупреждение',
                info: 'Информация',
                profile_saved: 'Профиль успешно сохранен!',
                profile_updated: 'Профиль успешно обновлен!',
                profile_deleted: 'Профиль успешно удален!',
                login_success: 'Вход выполнен успешно!',
                logout_success: 'Выход выполнен успешно!',
                registration_success: 'Регистрация прошла успешно!',
                
                // Errors
                login_required: 'Требуется вход в систему',
                access_denied: 'Доступ запрещен',
                profile_not_found: 'Профиль не найден',
                user_not_found: 'Пользователь не найден',
                invalid_credentials: 'Неверные учетные данные',
                username_taken: 'Имя пользователя уже занято',
                password_too_short: 'Пароль слишком короткий',
                email_invalid: 'Неверный email',
                required_field: 'Это поле обязательно для заполнения',
                min_length: 'Минимальная длина {min} символов',
                max_length: 'Максимальная длина {max} символов',
                
                // Actions
                create_profile: 'Создать профиль',
                edit_design: 'Редактировать дизайн',
                copy_link: 'Копировать ссылку',
                share_profile: 'Поделиться профилем',
                view_profile: 'Просмотреть профиль',
                update_profile: 'Обновить профиль',
                delete_profile: 'Удалить профиль',
                
                // Footer
                about: 'О нас',
                contact: 'Контакты',
                privacy: 'Конфиденциальность',
                terms: 'Условия',
                support: 'Поддержка',
                help: 'Помощь',
                faq: 'FAQ',
                documentation: 'Документация',
                copyright: 'Все права защищены.'
            },
            uk: {
                // Navigation
                home: 'Головна',
                profiles: 'Профілі',
                leaders: 'Лідери',
                login: 'Увійти',
                create: 'Створити профіль',
                editor: 'Редактор',
                
                // Hero
                hero_subtitle: '— створіть свій стильний профіль, зберіть усі посилання та поділіться ним безкоштовно',
                hero_description: 'Об\'єднайте всі свої соцмережі в одному місці. Ідеально для блогерів, геймерів та підприємців!',
                
                // Common
                loading: 'Завантаження...',
                save: 'Зберегти',
                cancel: 'Скасувати',
                edit: 'Редагувати',
                delete: 'Видалити',
                close: 'Закрити',
                back: 'Назад',
                next: 'Далі',
                previous: 'Назад',
                search: 'Пошук',
                submit: 'Відправити',
                reset: 'Скинути',
                preview: 'Попередній перегляд',
                fullscreen: 'Повний екран',
                
                // Profile
                profile: 'Профіль',
                my_profile: 'Мій профіль',
                edit_profile: 'Редагувати профіль',
                profile_editor: 'Редактор профілю',
                avatar: 'Аватар',
                name: 'Ім\'я',
                bio: 'Про себе',
                social_links: 'Соціальні посилання',
                stats: 'Статистика',
                achievements: 'Досягнення',
                views: 'переглядів',
                likes: 'лайків',
                posts: 'постів',
                
                // Forms
                username: 'Ім\'я користувача',
                password: 'Пароль',
                email: 'Email',
                description: 'Опис',
                website: 'Веб-сайт',
                phone: 'Телефон',
                
                // Form titles
                create_profile_title: 'Створи цифрову візитку за 1 хвилину',
                create_profile_subtitle: 'Приєднуйся до Bionrgg – об\'єднуй всі свої соцмережі в одному профілі.',
                
                // Form fields
                basic_info: 'Основна інформація',
                username_required: 'Ім\'я користувача *',
                password_required: 'Пароль *',
                profile_description: 'Опис профілю',
                tell_about_yourself: 'Розкажіть про себе...',
                social_networks: 'Соціальні мережі',
                create_profile: 'Створити профіль',
                
                // Login
                login_to_bionrgg: 'Увійти в Bionrgg',
                login_subtitle: 'Керуйте своєю цифровою візиткою та об\'єднуйте всі соцмережі в одному місці.',
                
                // Profiles
                leaders_table: 'ТАБЛИЦЯ ЛІДЕРІВ | ТОП 5',
                leaders_subtitle: 'Таблиця лідерів на основі переглядів',
                loading_profiles: 'Завантаження профілів...',
                loading_error: 'Помилка завантаження',
                loading_error_text: 'Не вдалося завантажити профілі. Спробуйте пізніше.',
                try_again: 'Спробувати знову',
                
                // My Profile
                edit_profile: 'Редагувати профіль',
                edit_profile_subtitle: 'Оновіть інформацію та налаштуйте дизайн вашої цифрової візитки',
                edit_design: 'Редагувати дизайн',
                
                // Additional texts
                no_account: 'Немає акаунту?',
                how_to_create_profile: 'Як створити свій профіль?',
                simple_process: 'Простий процес створення вашої цифрової візитки',
                click_create_profile: 'Натисніть «Створити профіль»',
                go_to_registration: 'Перейдіть на сторінку реєстрації та заповніть базову інформацію',
                fill_all_fields: 'Заповніть усі поля',
                add_description_links: 'Додайте опис, посилання на соцмережі та налаштуйте дизайн',
                enjoy_using: 'Користуйтеся із задоволенням :)',
                share_profile_views: 'Поділіться своїм профілем та збирайте перегляди',
                
                // Design
                background: 'Фон',
                theme: 'Тема',
                color: 'Колір',
                gradient: 'Градієнт',
                solid: 'Суцільний',
                image: 'Зображення',
                dark: 'Темна',
                light: 'Світла',
                neon: 'Неон',
                
                // Messages
                success: 'Успіх',
                error: 'Помилка',
                warning: 'Попередження',
                info: 'Інформація',
                profile_saved: 'Профіль успішно збережено!',
                profile_updated: 'Профіль успішно оновлено!',
                profile_deleted: 'Профіль успішно видалено!',
                login_success: 'Вхід виконано успішно!',
                logout_success: 'Вихід виконано успішно!',
                registration_success: 'Реєстрація пройшла успішно!',
                
                // Errors
                login_required: 'Потрібно увійти в систему',
                access_denied: 'Доступ заборонено',
                profile_not_found: 'Профіль не знайдено',
                user_not_found: 'Користувач не знайдено',
                invalid_credentials: 'Невірні облікові дані',
                username_taken: 'Ім\'я користувача вже зайнято',
                password_too_short: 'Пароль занадто короткий',
                email_invalid: 'Невірний email',
                required_field: 'Це поле обов\'язкове для заповнення',
                min_length: 'Мінімальна довжина {min} символів',
                max_length: 'Максимальна довжина {max} символів',
                
                // Actions
                create_profile: 'Створити профіль',
                edit_design: 'Редагувати дизайн',
                copy_link: 'Копіювати посилання',
                share_profile: 'Поділитися профілем',
                view_profile: 'Переглянути профіль',
                update_profile: 'Оновити профіль',
                delete_profile: 'Видалити профіль',
                
                // Footer
                about: 'Про нас',
                contact: 'Контакти',
                privacy: 'Конфіденційність',
                terms: 'Умови',
                support: 'Підтримка',
                help: 'Допомога',
                faq: 'FAQ',
                documentation: 'Документація',
                copyright: 'Всі права захищені.'
            }
        };
        
        this.init();
    }

    init() {
        // Load saved language or default to English
        const savedLanguage = localStorage.getItem('language');
        this.currentLanguage = savedLanguage || 'en';
        
        // Apply language immediately
        this.applyLanguage();
        
        // Setup language selector if exists
        this.setupLanguageSelector();
    }

    setupLanguageSelector() {
        const languageSelects = document.querySelectorAll('.language-select, #languageSelect');
        languageSelects.forEach(select => {
            select.value = this.currentLanguage;
            select.addEventListener('change', (e) => {
                this.setLanguage(e.target.value);
            });
        });
    }

    setLanguage(lang) {
        if (this.translations[lang]) {
            this.currentLanguage = lang;
            localStorage.setItem('language', lang);
            this.applyLanguage();
        }
    }

    applyLanguage() {
        // Update all elements with data-translate attribute
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            const translation = this.getTranslation(key);
            if (translation) {
                element.textContent = translation;
            }
        });

        // Update placeholder attributes
        document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
            const key = element.getAttribute('data-translate-placeholder');
            const translation = this.getTranslation(key);
            if (translation) {
                element.placeholder = translation;
            }
        });

        // Update language selectors
        document.querySelectorAll('.language-select, #languageSelect').forEach(select => {
            select.value = this.currentLanguage;
        });

        // Update document language
        document.documentElement.lang = this.currentLanguage;
    }

    getTranslation(key) {
        return this.translations[this.currentLanguage]?.[key] || this.translations['en']?.[key] || key;
    }

    translate(key, params = {}) {
        let translation = this.getTranslation(key);
        
        // Replace parameters
        Object.keys(params).forEach(param => {
            translation = translation.replace(`{${param}}`, params[param]);
        });
        
        return translation;
    }
}

// Create global instance
window.LanguageManager = new LanguageManager();

// Export for use in other scripts
window.Language = window.LanguageManager;