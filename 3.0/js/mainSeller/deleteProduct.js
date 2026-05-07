// js/mainSeller/deleteProduct.js - исправленная версия

document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    const productNameToDelete = document.getElementById('deleteProductName');
    const confirmDeleteBtn = document.querySelector('.confirm-delete-btn');
    const cancelBtns = document.querySelectorAll('.cancel-btn, .close-modal');
    
    let productToDeleteId = null;
    let productCardToDelete = null;

    // Используем делегирование для динамических кнопок
    document.body.addEventListener('click', function(e) {
        // Ищем кнопку удаления (родительский элемент или саму кнопку)
        const deleteBtn = e.target.closest('.delete-product-btn');
        
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            productToDeleteId = deleteBtn.getAttribute('data-product-id');
            const productName = deleteBtn.getAttribute('data-product-name');
            productCardToDelete = deleteBtn.closest('.product-card');
            
            if (productNameToDelete) {
                productNameToDelete.textContent = productName;
            }
            if (deleteModal) {
                deleteModal.style.display = 'flex';
            }
        }
    });
    
    function closeDeleteModal() {
        if (deleteModal) {
            deleteModal.style.display = 'none';
        }
        productToDeleteId = null;
        productCardToDelete = null;
    }
    
    // Закрытие по кнопкам отмены
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', closeDeleteModal);
    });
    
    // Закрытие по клику вне окна
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteModal && deleteModal.style.display === 'flex') {
            closeDeleteModal();
        }
    });
    
    // Подтверждение удаления
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (productToDeleteId) {
                deleteProduct(productToDeleteId);
            }
        });
    }
    
    function deleteProduct(productId) {
        if (!confirmDeleteBtn) return;
        
        const originalText = confirmDeleteBtn.textContent;
        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.textContent = 'Удаление...';
        
        fetch('./php/masterData/deleteProduct.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + encodeURIComponent(productId)
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                showMessage('Товар успешно удален', 'success');
                
                // Удаляем карточку из DOM
                if (productCardToDelete && productCardToDelete.parentNode) {
                    productCardToDelete.remove();
                }
                
                // Проверяем, остались ли товары
                const productGrid = document.querySelector('.product-grid');
                if (productGrid && productGrid.children.length === 0) {
                    productGrid.innerHTML = '<div class="no-products">У вас пока нет товаров</div>';
                }
            } else if (result === 'not_owner') {
                showMessage('У вас нет прав для удаления этого товара', 'error');
            } else {
                showMessage('Ошибка при удалении товара', 'error');
            }
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ошибка при удалении товара', 'error');
            closeDeleteModal();
        })
        .finally(() => {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = originalText;
        });
    }
});