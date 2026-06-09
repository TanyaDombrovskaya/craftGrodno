// toogleMenu.js - полная замена

document.addEventListener('DOMContentLoaded', function() {
    console.log('Скрипт загружен');
    
    const menuToggle = document.querySelector('.menu-toggle');
    const sellerSidebar = document.querySelector('.seller-sidebar');
    
    console.log('menuToggle найден:', menuToggle);
    console.log('sellerSidebar найден:', sellerSidebar);
    
    // Создаем оверлей
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay && sellerSidebar) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
        console.log('Оверлей создан');
    }
    
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    function openSidebar() {
        console.log('openSidebar вызван');
        if (!sellerSidebar) return;
        sellerSidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.classList.add('menu-open');
        if (menuToggle) menuToggle.classList.add('active');
    }
    
    function closeSidebar() {
        console.log('closeSidebar вызван');
        if (!sellerSidebar) return;
        sellerSidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('menu-open');
        if (menuToggle) menuToggle.classList.remove('active');
    }
    
    // Клик по бургеру для страницы продавца
    if (menuToggle && sellerSidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Клик по бургеру (продавец)');
            if (sellerSidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }
    
    // Закрытие по оверлею для продавца
    if (overlay) {
        overlay.addEventListener('click', function() {
            console.log('Клик по оверлею (продавец)');
            closeSidebar();
        });
    }
    
    // Закрытие по ESC для продавца
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sellerSidebar && sellerSidebar.classList.contains('open')) {
            console.log('Нажат ESC (продавец)');
            closeSidebar();
        }
    });
    
    // При изменении размера окна для продавца
    window.addEventListener('resize', function() {
        if (!isMobile() && sellerSidebar && sellerSidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
    
    // Добавляем стили для оверлея продавца если их нет
    if (!document.querySelector('#mobile-styles')) {
        const style = document.createElement('style');
        style.id = 'mobile-styles';
        style.textContent = `
            .sidebar-overlay {
                position: fixed;
                top: 60px;
                left: 0;
                width: 100%;
                height: calc(100vh - 60px);
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }
            body.menu-open {
                overflow: hidden;
            }
            @media (max-width: 768px) {
                .seller-sidebar {
                    position: fixed !important;
                    top: 60px !important;
                    left: -100% !important;
                    width: 85% !important;
                    max-width: 300px !important;
                    height: calc(100vh - 60px) !important;
                    background: white !important;
                    z-index: 999 !important;
                    transition: left 0.3s ease !important;
                    overflow-y: auto !important;
                }
                .seller-sidebar.open {
                    left: 0 !important;
                }
                .seller-page {
                    margin-top: 60px;
                }
            }
        `;
        document.head.appendChild(style);
        console.log('Стили для продавца добавлены');
    }
});

// ============================================
// ========== ЛОГИКА ДЛЯ СТРАНИЦЫ ПОЛЬЗОВАТЕЛЯ (mainUser) ==========
// ============================================

(function() {
    // Ждем полной загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserMenu);
    } else {
        initUserMenu();
    }
    
    function initUserMenu() {
        console.log('Инициализация меню пользователя (mainUser)');
        
        const userNavLinks = document.querySelector('.nav-links');
        const userMenuToggle = document.querySelector('.menu-toggle');
        
        console.log('userNavLinks найден:', userNavLinks);
        console.log('userMenuToggle найден:', userMenuToggle);
        
        // Если нет .nav-links или есть .seller-sidebar - выходим (это страница продавца)
        if (!userNavLinks || document.querySelector('.seller-sidebar')) {
            console.log('Страница не подходит для меню пользователя mainUser');
            return;
        }
        
        // Создаем оверлей для меню пользователя
        let userOverlay = document.querySelector('.nav-overlay');
        if (!userOverlay) {
            userOverlay = document.createElement('div');
            userOverlay.className = 'nav-overlay';
            document.body.appendChild(userOverlay);
            console.log('Оверлей для пользователя создан');
        }
        
        function isUserMobile() {
            return window.innerWidth <= 768;
        }
        
        function openUserMenu() {
            console.log('openUserMenu вызван');
            if (!userNavLinks) return;
            userNavLinks.classList.add('active');
            if (userMenuToggle) userMenuToggle.classList.add('active');
            if (userOverlay) userOverlay.classList.add('active');
            document.body.classList.add('menu-open');
            document.body.style.overflow = 'hidden';
        }
        
        function closeUserMenu() {
            console.log('closeUserMenu вызван');
            if (!userNavLinks) return;
            userNavLinks.classList.remove('active');
            if (userMenuToggle) userMenuToggle.classList.remove('active');
            if (userOverlay) userOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            document.body.style.overflow = '';
        }
        
        function toggleUserMenu() {
            console.log('toggleUserMenu вызван');
            if (!userNavLinks) return;
            if (userNavLinks.classList.contains('active')) {
                closeUserMenu();
            } else {
                openUserMenu();
            }
        }
        
        // Клик по бургеру на странице пользователя
        if (userMenuToggle) {
            const newToggle = userMenuToggle.cloneNode(true);
            userMenuToggle.parentNode.replaceChild(newToggle, userMenuToggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Клик по бургеру (пользователь mainUser)');
                toggleUserMenu();
            });
        }
        
        // Закрытие по клику на оверлей
        if (userOverlay) {
            userOverlay.addEventListener('click', function() {
                console.log('Клик по оверлею (пользователь mainUser)');
                closeUserMenu();
            });
        }
        
        // Закрытие по клику на ссылки в меню
        if (userNavLinks) {
            userNavLinks.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    console.log('Клик по ссылке меню');
                    closeUserMenu();
                });
            });
        }
        
        // Закрытие по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && userNavLinks && userNavLinks.classList.contains('active')) {
                console.log('Нажат ESC (пользователь mainUser)');
                closeUserMenu();
            }
        });
        
        // При изменении размера окна
        window.addEventListener('resize', function() {
            if (!isUserMobile() && userNavLinks && userNavLinks.classList.contains('active')) {
                console.log('Изменение размера окна, закрываем меню');
                closeUserMenu();
            }
        });
        
        // Добавляем стили для оверлея пользователя, если их нет
        if (!document.querySelector('#user-nav-styles')) {
            const userStyle = document.createElement('style');
            userStyle.id = 'user-nav-styles';
            userStyle.textContent = `
                .nav-overlay {
                    position: fixed;
                    top: 60px;
                    left: 0;
                    width: 100%;
                    height: calc(100vh - 60px);
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 998;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                }
                .nav-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }
                @media (max-width: 768px) {
                    .nav-links {
                        position: fixed !important;
                        top: 60px !important;
                        left: -100% !important;
                        width: 280px !important;
                        height: calc(100vh - 60px) !important;
                        background: white !important;
                        flex-direction: column !important;
                        align-items: flex-start !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        gap: 0 !important;
                        transition: left 0.3s ease !important;
                        z-index: 999 !important;
                        box-shadow: 2px 0 10px rgba(0,0,0,0.1) !important;
                        display: flex !important;
                        overflow-y: auto !important;
                    }
                    .nav-links.active {
                        left: 0 !important;
                    }
                    .nav-links .nav-link {
                        width: 100% !important;
                        padding: 14px 20px !important;
                        font-size: 16px !important;
                        font-weight: 500 !important;
                        color: #D97706 !important;
                        background: white !important;
                        border-bottom: 1px solid #e5e7eb !important;
                        text-decoration: none !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                    }
                    .nav-links .nav-link:hover {
                        background: #FFF7ED !important;
                        color: #B45309 !important;
                        padding-left: 24px !important;
                    }
                    .nav-links .nav-link .cart-counter {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        background: #D97706 !important;
                        color: white !important;
                        border-radius: 20px !important;
                        padding: 2px 8px !important;
                        font-size: 12px !important;
                        font-weight: 600 !important;
                        min-width: 24px !important;
                        height: 22px !important;
                        margin-left: auto !important;
                        flex-shrink: 0 !important;
                    }
                    .menu-toggle {
                        display: flex !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                }
            `;
            document.head.appendChild(userStyle);
            console.log('Стили для пользователя mainUser добавлены');
        }
        
        console.log('Меню пользователя mainUser инициализировано');
    }
})();

// ============================================
// ========== ЛОГИКА ДЛЯ СТРАНИЦЫ ПРОФИЛЯ ПОЛЬЗОВАТЕЛЯ (userProfile) ==========
// ============================================

(function() {
    // Ждем полной загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfileMenu);
    } else {
        initProfileMenu();
    }
    
    function initProfileMenu() {
        console.log('Инициализация меню профиля пользователя (userProfile)');
        
        const profileSidebar = document.querySelector('.profile-sidebar');
        const profileMenuToggle = document.querySelector('.menu-toggle');
        
        console.log('profileSidebar найден:', profileSidebar);
        console.log('profileMenuToggle найден:', profileMenuToggle);
        
        // Если нет .profile-sidebar - выходим
        if (!profileSidebar) {
            console.log('Страница не подходит для меню профиля пользователя');
            return;
        }
        
        // Создаем оверлей для боковой панели профиля
        let profileOverlay = document.querySelector('.profile-sidebar-overlay');
        if (!profileOverlay) {
            profileOverlay = document.createElement('div');
            profileOverlay.className = 'profile-sidebar-overlay';
            document.body.appendChild(profileOverlay);
            console.log('Оверлей для профиля создан');
        }
        
        function isProfileMobile() {
            return window.innerWidth <= 768;
        }
        
        function openProfileSidebar() {
            console.log('openProfileSidebar вызван');
            if (!profileSidebar) return;
            profileSidebar.classList.add('open');
            if (profileOverlay) profileOverlay.classList.add('active');
            document.body.classList.add('menu-open');
            if (profileMenuToggle) profileMenuToggle.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeProfileSidebar() {
            console.log('closeProfileSidebar вызван');
            if (!profileSidebar) return;
            profileSidebar.classList.remove('open');
            if (profileOverlay) profileOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            if (profileMenuToggle) profileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        function toggleProfileSidebar() {
            console.log('toggleProfileSidebar вызван');
            if (!profileSidebar) return;
            if (profileSidebar.classList.contains('open')) {
                closeProfileSidebar();
            } else {
                openProfileSidebar();
            }
        }
        
        // Клик по бургеру для страницы профиля
        if (profileMenuToggle) {
            // Удаляем старые обработчики
            const newToggle = profileMenuToggle.cloneNode(true);
            profileMenuToggle.parentNode.replaceChild(newToggle, profileMenuToggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Клик по бургеру (профиль пользователя)');
                toggleProfileSidebar();
            });
        }
        
        // Закрытие по клику на оверлей
        if (profileOverlay) {
            profileOverlay.addEventListener('click', function() {
                console.log('Клик по оверлею (профиль пользователя)');
                closeProfileSidebar();
            });
        }
        
        // Закрытие по клику на ссылки в боковой панели профиля
        if (profileSidebar) {
            profileSidebar.querySelectorAll('.profile-nav-btn, .profile-logout-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Не закрываем при клике на кнопку выхода - пусть выполнится его действие
                    if (!this.classList.contains('profile-logout-btn')) {
                        console.log('Клик по кнопке навигации профиля');
                        closeProfileSidebar();
                    }
                });
            });
        }
        
        // Закрытие по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && profileSidebar && profileSidebar.classList.contains('open')) {
                console.log('Нажат ESC (профиль пользователя)');
                closeProfileSidebar();
            }
        });
        
        // При изменении размера окна
        window.addEventListener('resize', function() {
            if (!isProfileMobile() && profileSidebar && profileSidebar.classList.contains('open')) {
                console.log('Изменение размера окна, закрываем меню профиля');
                closeProfileSidebar();
            }
        });
        
        // Добавляем стили для оверлея профиля, если их нет
        if (!document.querySelector('#profile-sidebar-styles')) {
            const profileStyle = document.createElement('style');
            profileStyle.id = 'profile-sidebar-styles';
            profileStyle.textContent = `
                .profile-sidebar-overlay {
                    position: fixed;
                    top: 60px;
                    left: 0;
                    width: 100%;
                    height: calc(100vh - 60px);
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 998;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                }
                .profile-sidebar-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }
                @media (max-width: 768px) {
                    .profile-sidebar {
                        position: fixed !important;
                        top: 60px !important;
                        left: -100% !important;
                        width: 85% !important;
                        max-width: 300px !important;
                        height: calc(100vh - 60px) !important;
                        background: white !important;
                        z-index: 999 !important;
                        transition: left 0.3s ease !important;
                        overflow-y: auto !important;
                        border-radius: 0 !important;
                        padding: var(--spacing-4) !important;
                    }
                    .profile-sidebar.open {
                        left: 0 !important;
                    }
                    .profile-nav {
                        flex-direction: column !important;
                    }
                    .profile-nav-btn {
                        text-align: center !important;
                        padding: var(--spacing-3) !important;
                        width: 100% !important;
                    }
                    .profile-logout-btn {
                        text-align: center !important;
                        margin-top: var(--spacing-3) !important;
                    }
                    .profile-page {
                        margin-top: 60px;
                    }
                    .menu-toggle {
                        display: flex !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                }
            `;
            document.head.appendChild(profileStyle);
            console.log('Стили для профиля пользователя добавлены');
        }
        
        console.log('Меню профиля пользователя инициализировано');
    }
})();