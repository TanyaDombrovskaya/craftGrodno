// Функции для работы с модальными окнами
function showSuccessModal(message, redirectUrl = null) {
    const modal = document.getElementById('successModal');
    const messageElement = document.getElementById('modalMessage');
    const okButton = document.getElementById('modalOkButton');
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    
    if (modal) {
        modal.classList.add('active');
    }

    if (okButton) {
        okButton.onclick = function() {
            modal.classList.remove('active');
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        };
    }

    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        };
    }
}

// Функция для показа модального окна ошибки
function showErrorModal(message) {
    const modal = document.getElementById('successModal');
    const messageElement = document.getElementById('modalMessage');
    const titleElement = modal.querySelector('.modal-title');
    const okButton = document.getElementById('modalOkButton');
    
    if (titleElement) {
        titleElement.textContent = 'Ошибка';
        titleElement.style.color = '#de1111';
    }
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    
    if (modal) {
        modal.classList.add('active');
    }

    if (okButton) {
        okButton.onclick = function() {
            modal.classList.remove('active');
            if (titleElement) {
                titleElement.textContent = 'Успех!';
                titleElement.style.color = '#2e8b57';
            }
        };
    }

    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                if (titleElement) {
                    titleElement.textContent = 'Успех!';
                    titleElement.style.color = '#2e8b57';
                }
            }
        };
    }
}