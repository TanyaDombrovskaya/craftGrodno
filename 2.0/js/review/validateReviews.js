document.getElementById('submit-review-btn').addEventListener('click', function(e) {
    e.preventDefault();
    
    const reviewText = document.getElementById('reviewText');
    const reviewTextValue = reviewText.value.trim();

    let hasError = false;

    clearAllFieldErrors();

    if (!reviewTextValue) {
        showFieldError(reviewText, 'Заполните поле Отзыв');
        hasError = true;
    } else if (reviewTextValue.length < 10) {
        showFieldError(reviewText, 'Отзыв должен содержать минимум 10 символов');
        hasError = true;
    }

    if (hasError) {
        reviewText.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    document.getElementById('reviewForm').submit();
});

document.getElementById('reviewText').addEventListener('input', function() {
    if (this.classList.contains('error-input')) {
        clearFieldError(this);
    }
});