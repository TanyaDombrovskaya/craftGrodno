document.addEventListener('DOMContentLoaded', function() {
    initModal();
});

// Функция для инициализации модального окна
function initModal() {
    const modal = document.getElementById('sellerModal');
    const sellerName = document.getElementById('modalSellerName');
    const sellerPhone = document.getElementById('modalSellerPhone');
    const closeButtons = document.querySelectorAll('.close-modal, .close-button');
    
    if (!modal || !sellerName || !sellerPhone) {
        console.log('Modal elements not found, retrying...');
        setTimeout(initModal, 100); // Повторяем попытку через 100ms
        return;
    }
    
    // Используем делегирование событий для работы с динамически добавленными кнопками
    document.addEventListener('click', function(event) {
        // Проверяем, была ли нажата кнопка add-to-cart или её дочерний элемент
        const addToCartButton = event.target.closest('.add-to-cart');
        
        if (addToCartButton) {
            event.preventDefault();
            
            const name = addToCartButton.getAttribute('data-seller-name');
            const phone = addToCartButton.getAttribute('data-seller-phone');
            
            console.log('Button clicked:', { name, phone }); // Для отладки
            
            if (sellerName && sellerPhone && modal) {
                sellerName.textContent = name || 'Не указано';
                sellerPhone.textContent = phone || 'Не указано';
                modal.style.display = 'block';
            }
        }
    });
    
    // Обработчики закрытия модального окна
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Закрытие по клику вне модального окна
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Закрытие по клавише Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });
    
    console.log('Modal initialized successfully');
}

// Функция для переинициализации после динамической загрузки контента
function reinitModal() {
    initModal();
}