document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('sellerModal');
    const sellerName = document.getElementById('modalSellerName');
    const sellerPhone = document.getElementById('modalSellerPhone');
    const closeButtons = document.querySelectorAll('.close-modal, .close-button');
    
    // Обработчик для кнопок "Связаться с продавцом"
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-seller-name');
            const phone = this.getAttribute('data-seller-phone');
            
            sellerName.textContent = name;
            sellerPhone.textContent = phone;
            modal.style.display = 'block';
        });
    });
    
    // Закрытие модального окна
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Закрытие при клике вне окна
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Закрытие по ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            modal.style.display = 'none';
        }
    });
});