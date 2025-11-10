document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const productGrid = document.getElementById('productGrid');
    const productCards = productGrid.querySelectorAll('.product-card');
    
    function searchProducts() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        productCards.forEach(card => {
            const productName = card.getAttribute('data-product-name').toLowerCase();
            const productDescription = card.getAttribute('data-product-description').toLowerCase();
            
            if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', searchProducts);
    
    document.querySelector('.search-button').addEventListener('click', searchProducts);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchProducts();
        }
    });
});