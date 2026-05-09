// categoryCarousel.js - Карусель с 1 видимой карточкой

document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('categoryCarousel');
    const prevBtn = document.getElementById('prevCategoryBtn');
    const nextBtn = document.getElementById('nextCategoryBtn');
    
    let currentIndex = 0;
    let categories = [];
    let autoScrollInterval;

    if (!carousel) return;

    // Создаем контейнер для точек
    const dotsContainer = document.createElement('div');
    dotsContainer.className = 'carousel-dots';
    document.querySelector('.category-carousel-container').appendChild(dotsContainer);

    function updateCarousel() {
        const offset = -currentIndex * 100;
        carousel.style.transform = `translateX(${offset}%)`;
        
        const dots = document.querySelectorAll('.carousel-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }

    function updateDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        categories.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'carousel-dot';
            if (index === currentIndex) dot.classList.add('active');
            dot.addEventListener('click', () => {
                stopAutoScroll();
                currentIndex = index;
                updateCarousel();
                startAutoScroll();
            });
            dotsContainer.appendChild(dot);
        });
    }

    function nextSlide() {
        if (categories.length === 0) return;
        currentIndex = (currentIndex + 1) % categories.length;
        updateCarousel();
    }

    function prevSlide() {
        if (categories.length === 0) return;
        currentIndex = (currentIndex - 1 + categories.length) % categories.length;
        updateCarousel();
    }

    function startAutoScroll() {
        if (autoScrollInterval) clearInterval(autoScrollInterval);
        if (!categories.length) return;
        autoScrollInterval = setInterval(() => {
            nextSlide();
        }, 5000);
    }

    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
        }
    }

    function getProductCountText(count) {
        count = parseInt(count);
        if (count % 10 === 1 && count % 100 !== 11) return 'товар';
        if ([2, 3, 4].includes(count % 10) && ![12, 13, 14].includes(count % 100)) return 'товара';
        return 'товаров';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    function renderCategories() {
        if (!categories.length) {
            carousel.innerHTML = '<div class="no-products">Категории не найдены</div>';
            return;
        }
        
        let html = '';
        categories.forEach(category => {
            const imageUrl = category.image || './styles/image/icon.png';
            html += `
                <a href="./allProducts.php?category=${encodeURIComponent(category.categoryName)}" class="category-card">
                    <img class="category-bg" src="${imageUrl}" alt="${escapeHtml(category.categoryName)}" loading="lazy" onerror="this.src='./styles/image/icon.png'">
                    <div class="category-content">
                        <div class="category-name">${escapeHtml(category.categoryName)}</div>
                        <div class="category-count">${category.product_count} ${getProductCountText(category.product_count)}</div>
                    </div>
                </a>
            `;
        });
        carousel.innerHTML = html;
        updateDots();
    }

    function loadCategories() {
        carousel.innerHTML = '<div class="loading">Загрузка...</div>';
        
        fetch('./php/userData/getCategories.php')
            .then(response => response.json())
            .then(data => {
                categories = data;
                if (!categories || categories.length === 0) {
                    carousel.innerHTML = '<div class="no-products">Категории не найдены</div>';
                    return;
                }
                renderCategories();
                setTimeout(() => {
                    startAutoScroll();
                }, 100);
            })
            .catch(error => {
                console.error('Ошибка загрузки категорий:', error);
                carousel.innerHTML = '<div class="no-products">Ошибка загрузки категорий</div>';
            });
    }

    // Обработчики кнопок
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopAutoScroll();
            prevSlide();
            startAutoScroll();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopAutoScroll();
            nextSlide();
            startAutoScroll();
        });
    }

    // Остановка при наведении
    const container = document.querySelector('.category-carousel-container');
    if (container) {
        container.addEventListener('mouseenter', stopAutoScroll);
        container.addEventListener('mouseleave', startAutoScroll);
    }

    loadCategories();
});