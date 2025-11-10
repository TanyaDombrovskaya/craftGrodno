// Система фильтрации
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const sortFilter = document.getElementById('sortFilter');
    const applyFilters = document.getElementById('applyFilters');
    const resetFilters = document.getElementById('resetFilters');
    const activeFilters = document.getElementById('activeFilters');
    const resultsCount = document.getElementById('resultsCount');
    const productGrid = document.getElementById('productGrid');
    
    let allProducts = [];
    let filtersApplied = false;
    
    // Собираем все продукты при загрузке
    function collectProducts() {
        const productCards = productGrid.querySelectorAll('.product-card');
        allProducts = Array.from(productCards).map(card => {
            const priceText = card.dataset.productPrice;
            const price = priceText ? parseFloat(priceText) : 0;
            
            const category = card.dataset.productCategory ? card.dataset.productCategory.toLowerCase() : '';
            
            return {
                element: card,
                name: card.dataset.productName ? card.dataset.productName.toLowerCase() : '',
                description: card.dataset.productDescription ? card.dataset.productDescription.toLowerCase() : '',
                price: price,
                category: category,
                originalElement: card
            };
        });
    }
    
    // Применяем фильтры
    function applyAllFilters() {
        if (!filtersApplied) return; 
        
        const category = categoryFilter.value.toLowerCase();
        const minPrice = priceMin.value ? parseFloat(priceMin.value) : 0;
        const maxPrice = priceMax.value ? parseFloat(priceMax.value) : Infinity;
        const sortBy = sortFilter.value;
        
        console.log('Applying filters:', { category, minPrice, maxPrice, sortBy }); // Для отладки
        
        let filteredProducts = allProducts.filter(product => {
            // Фильтр по категории
            if (category && product.category !== category) {
                return false;
            }
            
            // Фильтр по цене
            if (product.price < minPrice || product.price > maxPrice) {
                return false;
            }
            
            return true;
        });
        
        // Сортировка с проверкой на валидность цен
        filteredProducts.sort((a, b) => {
            const priceA = isNaN(a.price) ? 0 : a.price;
            const priceB = isNaN(b.price) ? 0 : b.price;
            
            switch (sortBy) {
                case 'name_asc':
                    return (a.name || '').localeCompare(b.name || '');
                case 'name_desc':
                    return (b.name || '').localeCompare(a.name || '');
                case 'price_asc':
                    return priceA - priceB;
                case 'price_desc':
                    return priceB - priceA;
                default:
                    return 0;
            }
        });
        
        // Обновляем отображение
        updateProductsDisplay(filteredProducts);
        updateActiveFilters(category, minPrice, maxPrice, sortBy);
        updateResultsCount(filteredProducts.length);
    }
    
    // Обновляем отображение продуктов
    function updateProductsDisplay(products) {
        // Сначала скрываем все продукты
        allProducts.forEach(product => {
            product.element.style.display = 'none';
            product.element.style.order = ''; // Сбрасываем порядок
        });
        
        // Показываем и упорядочиваем отфильтрованные продукты
        products.forEach((product, index) => {
            product.element.style.display = 'block';
            product.element.style.order = index; // Устанавливаем порядок для CSS Grid
        });
        
        // Если нет товаров, показываем сообщение
        if (products.length === 0) {
            showNoProductsMessage();
        } else {
            hideNoProductsMessage();
        }
    }
    
    // Показать сообщение "Товары не найдены"
    function showNoProductsMessage() {
        let noProductsMsg = productGrid.querySelector('.no-products-filtered');
        if (!noProductsMsg) {
            noProductsMsg = document.createElement('div');
            noProductsMsg.className = 'no-products no-products-filtered';
            noProductsMsg.innerHTML = `
                <h3>Товары не найдены</h3>
                <p>Попробуйте изменить параметры фильтрации</p>
            `;
            productGrid.appendChild(noProductsMsg);
        }
        noProductsMsg.style.display = 'block';
        noProductsMsg.style.order = '9999';
    }
    
    // Скрыть сообщение "Товары не найдены"
    function hideNoProductsMessage() {
        const noProductsMsg = productGrid.querySelector('.no-products-filtered');
        if (noProductsMsg) {
            noProductsMsg.style.display = 'none';
        }
    }
    
    // Обновляем активные фильтры
    function updateActiveFilters(category, minPrice, maxPrice, sortBy) {
        activeFilters.innerHTML = '';
        
        if (category) {
            addActiveFilter('Категория', categoryFilter.options[categoryFilter.selectedIndex].text, 'category');
        }
        
        if (minPrice > 0) {
            addActiveFilter('Цена от', `${minPrice} руб`, 'priceMin');
        }
        
        if (maxPrice < Infinity) {
            addActiveFilter('Цена до', `${maxPrice} руб`, 'priceMax');
        }
        
        if (sortBy && sortBy !== 'name_asc') {
            const sortText = getSortText(sortBy);
            addActiveFilter('Сортировка', sortText, 'sort');
        }
    }
    
    // Получить текст для сортировки
    function getSortText(sortBy) {
        const sortMap = {
            'name_asc': 'По названию (А-Я)',
            'name_desc': 'По названию (Я-А)',
            'price_asc': 'По цене (сначала дешевые)',
            'price_desc': 'По цене (сначала дорогие)',
            'popular': 'По популярности'
        };
        return sortMap[sortBy] || '';
    }
    
    // Добавляем активный фильтр
    function addActiveFilter(name, value, type) {
        const filterTag = document.createElement('div');
        filterTag.className = 'active-filter-tag';
        filterTag.innerHTML = `
            ${name}: ${value}
            <button class="remove-filter" data-type="${type}">&times;</button>
        `;
        activeFilters.appendChild(filterTag);
    }
    
    // Обновляем счетчик результатов
    function updateResultsCount(count) {
        resultsCount.textContent = `Найдено товаров: ${count}`;
    }
    
    // Сброс фильтров
    function resetAllFilters() {
        categoryFilter.value = '';
        priceMin.value = '';
        priceMax.value = '';
        sortFilter.value = 'name_asc';
        filtersApplied = false;
        
        allProducts.forEach(product => {
            product.element.style.display = 'block';
            product.element.style.order = '';
        });
        
        activeFilters.innerHTML = '';
        resultsCount.textContent = '';
        hideNoProductsMessage();
    }
    
    // Удаление конкретного фильтра
    function removeFilter(type) {
        switch (type) {
            case 'category':
                categoryFilter.value = '';
                break;
            case 'priceMin':
                priceMin.value = '';
                break;
            case 'priceMax':
                priceMax.value = '';
                break;
            case 'sort':
                sortFilter.value = 'name_asc';
                break;
        }

        applyFilters.click();
    }
    
    // Инициализация
    function initFilters() {
        collectProducts();
        
    // Автоматически применяем фильтр категории при загрузке, если она передана в URL
    if (categoryFilter.value) {
        filtersApplied = true;
        applyAllFilters();
    }

        applyFilters.addEventListener('click', function() {
            filtersApplied = true;
            applyAllFilters();
        });
        
        resetFilters.addEventListener('click', resetAllFilters);
        
        activeFilters.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-filter')) {
                const type = e.target.dataset.type;
                removeFilter(type);
            }
        });
    }
    
    initFilters();
    
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                const newProductCards = Array.from(mutation.addedNodes).some(node => 
                    node.nodeType === 1 && node.classList.contains('product-card')
                );
                if (newProductCards) {
                    collectProducts();
                    filtersApplied = false;
                }
            }
        });
    });
    
    observer.observe(productGrid, {
        childList: true,
        subtree: true
    });
});