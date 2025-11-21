document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    if (categorySelect && categorySelect.value !== '') {
        categorySelect.disabled = true;
    }

    checkMasterDataCompletion();
    
    const masterFormFields = document.querySelectorAll('#profile input, #profile select, #profile textarea');
    masterFormFields.forEach(field => {
        field.addEventListener('change', checkMasterDataCompletion);
        field.addEventListener('input', checkMasterDataCompletion);
    });
});

document.querySelector('.master-form').addEventListener('submit', function(e) {
    const categorySelect = document.getElementById('category');
    if (categorySelect && categorySelect.disabled) {
        categorySelect.disabled = false;
    }
});

const sections = [
    { id: 'add-product', content: '.product-form' },
    { id: 'products', content: '.product-grid' },
    { id: 'products', content: '.products-header' }
];

function checkMasterDataCompletion() {
    const requiredFields = [
        document.getElementById('master_name'),
        document.getElementById('direction'),
        document.getElementById('category'),
        document.getElementById('phone'),
        document.getElementById('about'),
        document.getElementById('experience')
    ];
    
    const allFieldsFilled = requiredFields.every(field => {
        if (!field) return false;
        
        if (field.type === 'select-one') {
            return field.value !== '';
        }
        
        if (field.type === 'textarea') {
            return field.value.trim() !== '';
        }
        
        return field.value.trim() !== '';
    });
    
    const isComplete = allFieldsFilled;
    
    updateSectionMessages(isComplete);
    
    return isComplete;
}

function updateSectionMessages(isComplete) {
    sections.forEach(section => {
        const sectionElement = document.getElementById(section.id);
        if (!sectionElement) return;
        
        const existingMessage = sectionElement.querySelector('.completion-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        const contentElement = sectionElement.querySelector(section.content);
        
        if (!isComplete) {
            if (contentElement) {
                contentElement.style.display = 'none';
            }
            
            const message = document.createElement('div');
            message.className = 'completion-message';
            message.innerHTML = `
                <div class="warning-message">
                    ⚠️ Чтобы добавить товары и просматривать свои товары, сначала заполните все обязательные поля в разделе "Личные данные мастера"
                </div>
            `;
            
            const title = sectionElement.querySelector('h2');
            if (title) {
                title.parentNode.insertBefore(message, title.nextSibling);
            }
        } else {
            if (contentElement) {
                contentElement.style.display = '';
            }
        }
    });
}