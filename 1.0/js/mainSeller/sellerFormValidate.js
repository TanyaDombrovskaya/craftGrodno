document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        if (!phoneInput.value) {
            phoneInput.value = '+375';
        }
        
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value;
            let cleanValue = value.replace(/\D/g, '');
            
            if (cleanValue.startsWith('375')) {
                cleanValue = cleanValue.substring(3);
            }
            
            cleanValue = cleanValue.substring(0, 9);
            
            let formattedValue = '+375 (';
            
            if (cleanValue.length > 0) {
                formattedValue += cleanValue.substring(0, 2);
                
                if (cleanValue.length > 2) {
                    formattedValue += ') ' + cleanValue.substring(2, 5);
                    
                    if (cleanValue.length > 5) {
                        formattedValue += '-' + cleanValue.substring(5, 7);
                        
                        if (cleanValue.length > 7) {
                            formattedValue += '-' + cleanValue.substring(7, 9);
                        }
                    }
                }
            }
            
            e.target.value = formattedValue;
            
            const cursorPosition = formattedValue.length;
            e.target.setSelectionRange(cursorPosition, cursorPosition);
        });

        phoneInput.addEventListener('keydown', function(e) {
            const cursorPosition = e.target.selectionStart;
            
            if (cursorPosition <= 6 && e.key !== 'Tab' && !e.key.startsWith('Arrow')) {
                e.preventDefault();
                
                if (/\d/.test(e.key)) {
                    const currentValue = e.target.value;
                    const cleanValue = currentValue.replace(/\D/g, '').substring(3);
                    const newValue = '+375 (' + (cleanValue + e.key).substring(0, 9);
                    e.target.value = formatPhoneNumber(newValue.replace(/\D/g, ''));
                    
                    const newCursorPosition = e.target.value.length;
                    e.target.setSelectionRange(newCursorPosition, newCursorPosition);
                }
            }
        });

        function formatPhoneNumber(cleanValue) {
            if (cleanValue.startsWith('375')) {
                cleanValue = cleanValue.substring(3);
            }
            cleanValue = cleanValue.substring(0, 9);
            
            let formatted = '+375 (';
            if (cleanValue.length > 0) {
                formatted += cleanValue.substring(0, 2);
                if (cleanValue.length > 2) {
                    formatted += ') ' + cleanValue.substring(2, 5);
                    if (cleanValue.length > 5) {
                        formatted += '-' + cleanValue.substring(5, 7);
                        if (cleanValue.length > 7) {
                            formatted += '-' + cleanValue.substring(7, 9);
                        }
                    }
                }
            }
            return formatted;
        }

        const form = phoneInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const phoneValue = phoneInput.value.replace(/\D/g, '');
                if (phoneValue.length !== 12) {
                    e.preventDefault();
                    phoneInput.focus();
                    return;
                }
            });
        }
    }

    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function(e) {
            let value = parseFloat(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            }
        });

        priceInput.addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            } else {
                e.target.value = value.toFixed(2);
            }
        });
    }

    const countInput = document.getElementById('count');
    if (countInput) {
        countInput.addEventListener('input', function(e) {
            let value = parseInt(e.target.value);
            if (value < 1 || isNaN(value)) {
                e.target.value = 1;
            }
        });
    }

    const experienceInput = document.getElementById('experience');
    if (experienceInput) {
        experienceInput.addEventListener('input', function(e) {
            let value = parseInt(e.target.value);
            if (value < 0 || isNaN(value)) {
                e.target.value = 0;
            }
            if (value > 50) {
                e.target.value = 50;
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showMessage('Операция выполнена успешно!', 'success');
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    } else if (urlParams.has('error')) {
        showMessage('Произошла ошибка. Попробуйте еще раз.', 'error');
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }

    checkUrlMessages();
});

function showMessage(text, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;
    messageDiv.textContent = text;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        messageDiv.style.background = 'linear-gradient(135deg, #2e8b57, #3cb371)';
    } else {
        messageDiv.style.background = 'linear-gradient(135deg, #ff6b6b, #ee5a52)';
    }

    document.body.appendChild(messageDiv);
    if (!document.querySelector('#message-styles')) {
        const style = document.createElement('style');
        style.id = 'message-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);
    }

    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 300);
    }, 3000);
}

function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success_message')) {
        const message = decodeURIComponent(urlParams.get('success_message'));
        showMessage(message, 'success');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.has('error_message')) {
        const message = decodeURIComponent(urlParams.get('error_message'));
        showMessage(message, 'error');
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ff6b6b';
                    
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message';
                        errorMsg.style.cssText = 'color: #ff6b6b; font-size: 0.8rem; margin-top: 0.25rem; display: block;';
                        errorMsg.textContent = 'Это поле обязательно для заполнения';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '';
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showMessage('Пожалуйста, заполните все обязательные поля', 'error');
            }
        });
    });
});