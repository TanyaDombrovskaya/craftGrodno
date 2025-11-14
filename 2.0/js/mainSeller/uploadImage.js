document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('product_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewText = imagePreview.querySelector('.preview-text');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Проверяем тип файла
            if (!file.type.startsWith('image/')) {
                alert('Пожалуйста, выберите файл изображения');
                imageInput.value = '';
                return;
            }
            
            // Проверяем размер файла (максимум 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Размер файла не должен превышать 5MB');
                imageInput.value = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Удаляем старый текст предпросмотра
                if (previewText) {
                    previewText.remove();
                }
                
                // Удаляем старое изображение, если есть
                const oldImage = imagePreview.querySelector('.preview-image');
                if (oldImage) {
                    oldImage.remove();
                }
                
                // Создаем новое изображение
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';
                img.alt = 'Предпросмотр изображения';
                
                imagePreview.appendChild(img);
                imagePreview.classList.add('has-image');
            };
            
            reader.readAsDataURL(file);
        } else {
            // Сбрасываем предпросмотр, если файл не выбран
            resetImagePreview();
        }
    });
    
    function resetImagePreview() {
        // Удаляем изображение и информацию
        const oldImage = imagePreview.querySelector('.preview-image');
        const fileInfo = imagePreview.querySelector('.image-info');
        
        if (oldImage) oldImage.remove();
        if (fileInfo) fileInfo.remove();
        
        // Восстанавливаем текст
        if (!previewText) {
            const newPreviewText = document.createElement('span');
            newPreviewText.className = 'preview-text';
            newPreviewText.textContent = 'Изображение не выбрано';
            imagePreview.appendChild(newPreviewText);
        } else {
            previewText.style.display = 'block';
        }
        
        imagePreview.classList.remove('has-image');
    }
    
    // Сброс предпросмотра при отправке формы
    document.getElementById('product-form').addEventListener('reset', function() {
        setTimeout(resetImagePreview, 0);
    });
});