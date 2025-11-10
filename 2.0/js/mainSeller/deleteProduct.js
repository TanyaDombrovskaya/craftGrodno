// Обработка удаления товаров
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    const deleteMessage = document.getElementById('deleteMessage');
    const productNameToDelete = document.getElementById('productNameToDelete');
    const closeModalBtn = deleteModal.querySelector('.close-modal');
    const cancelBtn = deleteModal.querySelector('.cancel-button');
    const confirmDeleteBtn = deleteModal.querySelector('.confirm-delete-button');
    
    let productToDeleteId = null;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-product-btn')) {
            productToDeleteId = e.target.getAttribute('data-product-id');
            const productName = e.target.getAttribute('data-product-name');
            
            productNameToDelete.textContent = productName;
            deleteModal.style.display = 'block';
        }
    });
    
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
        productToDeleteId = null;
    }
    
    closeModalBtn.addEventListener('click', closeDeleteModal);
    cancelBtn.addEventListener('click', closeDeleteModal);
    
    confirmDeleteBtn.addEventListener('click', function() {
        if (productToDeleteId) {
            deleteProduct(productToDeleteId);
        }
    });
    
    // Функция удаления товара
    function deleteProduct(productId) {
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
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                if (productCard) {
                    productCard.remove();
                }
                const productGrid = document.querySelector('.product-grid');
                if (productGrid && productGrid.children.length === 0) {
                    productGrid.innerHTML = '<div class="no-products">У вас пока нет товаров</div>';
                }
            } else {
                showMessage('Ошибка при удалении товара', 'error');
            }
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ошибка при удалении товара', 'error');
            closeDeleteModal();
        });
    }
});