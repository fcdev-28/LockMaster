document.addEventListener('DOMContentLoaded', () => {
    const errors = document.querySelectorAll('.error-message');

    errors.forEach(function (error) {
        setTimeout(() => {
            error.style.transition = 'opacity 0.3s ease';
            error.style.opacity = '0';
            setTimeout(() => error.remove(), 300);
        }, 3000);
    });
});